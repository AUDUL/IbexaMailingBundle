<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\ConfirmationToken;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Entity\Registration;
use CodeRhapsodie\IbexaMailingBundle\Entity\StatHit;
use CodeRhapsodie\IbexaMailingBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ibexamailing:install', description: 'Add IbexaMailing tables to database')]
class InstallCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Instead of trying to apply generated SQLs into EntityManager Storage Connection, output them.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $schemaTool = new SchemaTool($this->entityManager);
        $dumpSql = $input->getOption('dump-sql') === true;

        $ignore = $schemaTool->getCreateSchemaSql([]);

        $sqls = $schemaTool->getCreateSchemaSql([
            $this->entityManager->getClassMetadata(Broadcast::class),
            $this->entityManager->getClassMetadata(Campaign::class),
            $this->entityManager->getClassMetadata(ConfirmationToken::class),
            $this->entityManager->getClassMetadata(Mailing::class),
            $this->entityManager->getClassMetadata(MailingList::class),
            $this->entityManager->getClassMetadata(Registration::class),
            $this->entityManager->getClassMetadata(StatHit::class),
            $this->entityManager->getClassMetadata(User::class),
        ]);

        $sqls = array_diff($sqls, $ignore);

        if ($dumpSql) {
            foreach ($sqls as $sql) {
                $io->writeln($sql);
            }

            return parent::SUCCESS;
        }

        foreach ($sqls as $sql) {
            $this->entityManager->getConnection()->executeQuery($sql);
        }

        $io->info('Tables created');

        return parent::SUCCESS;
    }
}
