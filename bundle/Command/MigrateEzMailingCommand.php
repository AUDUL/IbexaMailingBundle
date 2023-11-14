<?php


declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Core\IOService;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateEzMailingCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ConfigResolverInterface
     */
    public const DEFAULT_FALLBACK_LOCATION_ID = 2;

    public const DUMP_FOLDER = 'migrate/ibexamailing';

    private readonly Connection $connection;

    public function __construct(
        private readonly IOService              $ioService,
        private readonly EntityManagerInterface $entityManager,
        private readonly Repository             $ezRepository,
    )
    {
        parent::__construct();
        $this->connection = $this->entityManager->getConnection();
    }

    protected function configure(): void
    {
        $this
            ->setName('ibexamailing:migrate:ibexamailing')
            ->setDescription('Import database from the old one.')
            ->addOption('export', null, InputOption::VALUE_NONE, 'Export from old DB to json files')
            ->addOption('import', null, InputOption::VALUE_NONE, 'Import from json files to new DB')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean the existing data')
            ->setHelp('Run ibexamailing:migrate:ibexamailing --export|--import|--clean');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Update the Database with Custom IbexaMailing Tables');

        if ($input->getOption('export')) {
            $this->export();
        } elseif ($input->getOption('import')) {
            $this->import();
        } elseif ($input->getOption('clean')) {
            $this->clean();
        } else {
            $this->io->error('No export or import option found. Run ibexamailing:migrate:ibexamailing --export|--import');
        }

        return Command::SUCCESS;
    }

    private function export(): void
    {
        // clean the 'ibexamailing' dir
        $this->ioService->cleanDir(self::DUMP_FOLDER);
        $this->io->section('Cleaned the folder with json files.');
        $this->io->section('Exporting from old database to json files.');

        $contentLanguageService = $this->ezRepository->getContentLanguageService();
        $defaultLanguageCode = $contentLanguageService->getDefaultLanguageCode();

        $lists = $campaigns = $users = [];

        $mailingCounter = $registrationCounter = 0;

        // Lists

        $sql = 'SELECT id, name, lang FROM ezmailingmailinglist WHERE draft = 0';

        $list_rows = $this->runQuery($sql);
        foreach ($list_rows as $list_row) {
            $fileName = $this->ioService->saveFile(
                self::DUMP_FOLDER . "/list/list_{$list_row['id']}.json",
                json_encode([$list_row['lang'] => $list_row['name']]) // Approve should be false when importing
            );
            $lists[] = pathinfo($fileName)['filename'];
        }

        // Campaigns

        $sql = 'SELECT id, subject, sender_name, sender_email, report_email, destination_mailing_list ';

        $sql .= 'FROM ezmailingcampaign WHERE draft = 0';

        $campaign_rows = $this->runQuery($sql);
        foreach ($campaign_rows as $campaign_row) {
            $fileName = $this->ioService->saveFile(
                self::DUMP_FOLDER . "/campaign/campaign_{$campaign_row['id']}.json",
                json_encode(
                    [
                        'name' => [$defaultLanguageCode => $campaign_row['subject']],
                        'senderName' => $campaign_row['sender_name'],
                        'senderEmail' => $campaign_row['sender_email'],
                        'reportEmail' => $campaign_row['report_email'],
                        'mailing_list' => $campaign_row['destination_mailing_list'],
                    ]
                )
            );
            $campaigns[] = pathinfo($fileName)['filename'];
        }

        // Users
        $sql = 'SELECT id, email, first_name, last_name, origin FROM ezmailinguser WHERE draft = 0 ';

        $sql .= 'AND (id, email) in (select max(id), email from ezmailinguser group by email)';

        $user_rows = $this->runQuery($sql);
        foreach ($user_rows as $user_row) {
            $sql = 'SELECT mailinglist_id, state FROM ezmailingregistration WHERE mailing_user_id = ?';
            $subscription_rows = $this->runQuery($sql, [$user_row['id']]);
            $subscriptions = [];
            foreach ($subscription_rows as $subscription_row) {
                $subscriptions[] = [
                    'mailinglist_id' => $subscription_row['mailinglist_id'],
                    'approved' => 20 === $subscription_row['state'],
                ];
                ++$registrationCounter;
            }

            $fileName = $this->ioService->saveFile(
                self::DUMP_FOLDER . "/user/user_{$user_row['id']}.json",
                json_encode(
                    [
                        'email' => $user_row['email'],
                        'firstName' => $user_row['first_name'],
                        'lastName' => $user_row['last_name'],
                        'origin' => $user_row['origin'],
                        'subscriptions' => $subscriptions,
                    ]
                )
            );
            $users[] = pathinfo($fileName)['filename'];
        }

        $this->ioService->saveFile(
            self::DUMP_FOLDER . '/manifest.json',
            json_encode(['lists' => $lists, 'campaigns' => $campaigns, 'users' => $users])
        );
        $this->io->section(
            'Total: ' . count($lists) . ' lists, ' . count($campaigns) . ' campaigns, ' . $mailingCounter . ' mailings, ' .
            count($users) . ' users, ' . $registrationCounter . ' registrations.'
        );
        $this->io->success('Export done.');
    }

    private function import(): void
    {
        // Clear the tables, reset the IDs
        $this->clean();
        $this->io->section('Importing from json files to new database.');

        $manifest = $this->ioService->readFile(self::DUMP_FOLDER . '/manifest.json');
        $fileNames = json_decode($manifest);

        // Lists
        $listCounter = $campaignCounter = $mailingCounter = $userCounter = $registrationCounter = 0;

        $listIds = [];

        $mailingListRepository = $this->entityManager->getRepository(MailingList::class);
        $userRepository = $this->entityManager->getRepository(User::class);

        foreach ($fileNames->lists as $listFile) {
            $listData = json_decode($this->ioService->readFile(self::DUMP_FOLDER . '/list/' . $listFile . '.json'));
            $mailingList = new MailingList();
            $mailingList->setNames((array)$listData);
            $mailingList->setWithApproval(false);
            $this->entityManager->persist($mailingList);
            ++$listCounter;
            $this->entityManager->flush();
            $listIds[explode('_', $listFile)[1]] = $mailingList->getId();
        }

        // Campaigns
        foreach ($fileNames->campaigns as $campaignFile) {
            $campaignData = json_decode(
                $this->ioService->readFile(self::DUMP_FOLDER . '/campaign/' . $campaignFile . '.json')
            );
            $campaign = new Campaign();
            $campaign->setNames((array)$campaignData->name);
            $campaign->setReportEmail($campaignData->reportEmail);
            $campaign->setSenderEmail($campaignData->senderEmail);
            $campaign->setReturnPathEmail('');
            $campaign->setSenderName($campaignData->senderName);
            $campaign->setLocationId(self::DEFAULT_FALLBACK_LOCATION_ID);

            if (!empty($campaignData->mailing_list)) {
                $mailingLists = explode(':', $campaignData->mailing_list);
                foreach ($mailingLists as $mailingListId) {
                    if (\array_key_exists($mailingListId, $listIds)) {
                        /* @var MailingList $mailingList */
                        $mailingList = $mailingListRepository->findOneBy(
                            ['id' => $listIds[$mailingListId]]
                        );
                        if (null !== $mailingList) {
                            $campaign->addMailingList($mailingList);
                        }
                    }
                }
            }
            $this->entityManager->persist($campaign);
            ++$campaignCounter;
        }

        // Users & Subscriptions
        foreach ($fileNames->users as $userFile) {
            $userData = json_decode($this->ioService->readFile(self::DUMP_FOLDER . '/user/' . $userFile . '.json'));

            // check if email already exists
            $existingUser = $userRepository->findOneBy(['email' => $userData->email]);
            if (null === $existingUser) {
                $user = new User();
                $user
                    ->setEmail($userData->email)
                    ->setFirstName($userData->firstName)
                    ->setLastName($userData->lastName)
                    ->setStatus('confirmed')
                    ->setOrigin($userData->origin);

                foreach ($userData->subscriptions as $subscription) {
                    if (\array_key_exists($subscription->mailinglist_id, $listIds)) {
                        /* @var MailingList $mailingList */
                        $mailingList = $mailingListRepository->findOneBy(
                            ['id' => $listIds[$subscription->mailinglist_id]]
                        );
                        if (null !== $mailingList) {
                            $registration = new Registration();
                            $registration->setMailingList($mailingList);
                            $registration->setApproved($subscription->approved);
                            $user->addRegistration($registration);
                            ++$registrationCounter;
                        }
                    }
                }
                $this->entityManager->persist($user);
                ++$userCounter;
            }
        }
        $this->entityManager->flush();

        $this->io->section(
            'Total: ' . $listCounter . ' lists, ' . $campaignCounter . ' campaigns, ' . $mailingCounter . ' mailings, ' .
            $userCounter . ' users, ' . $registrationCounter . ' registrations.'
        );
        $this->io->success('Import done.');
    }

    private function clean(): void
    {
        // We don't run TRUNCATE command here because of foreign keys constraints
        $this->connection->executeQuery('DELETE FROM ibexamailing_stats_hit');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_stats_hit AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_broadcast');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_broadcast AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_mailing');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_mailing AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_campaign_mailinglists_destination');
        $this->connection->executeQuery('DELETE FROM ibexamailing_campaign');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_campaign AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_confirmation_token');
        $this->connection->executeQuery('DELETE FROM ibexamailing_registrations');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_registrations AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_mailing_list');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_mailing_list AUTO_INCREMENT = 1');
        $this->connection->executeQuery('DELETE FROM ibexamailing_user');
        $this->connection->executeQuery('ALTER TABLE ibexamailing_user AUTO_INCREMENT = 1');
        $this->io->section('Current tables in the new database have been cleaned.');
    }

    private function runQuery(string $sql, array $parameters = []): array
    {
        $stmt = $this->connection->prepare($sql);
        for ($i = 1, $iMax = count($parameters); $i <= $iMax; ++$i) {
            $stmt->bindValue($i, $parameters[$i - 1]);
        }
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }
}
