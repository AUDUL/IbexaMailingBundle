<?php

namespace Novactive\Bundle\eZMailingBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Novactive\Bundle\eZMailingBundle\Core\Mailer\Mailing;
use Novactive\Bundle\eZMailingBundle\Core\Provider\MailingContent;
use Novactive\Bundle\eZMailingBundle\Entity\Broadcast;
use Novactive\Bundle\eZMailingBundle\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'novaezmailing:send:mailing-subprocess', hidden: true)]
class SendMailingSubProcessCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface                                    $entityManager,
        private readonly MailingContent                                            $mailingContent,
        private readonly \Novactive\Bundle\eZMailingBundle\Core\Provider\Broadcast $broadcastProvider,
        private readonly Mailing                                                   $mailing
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(name: 'broadcast-id', mode: InputOption::VALUE_REQUIRED)
            ->addOption(name: 'users-id', mode: InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $broadcastRepository = $this->entityManager->getRepository(Broadcast::class);
        $usersId = explode(',', $input->getOption('users-id'));
        /** @var \Novactive\Bundle\eZMailingBundle\Entity\Broadcast $broadcast */
        $broadcast = $broadcastRepository->find($input->getOption('broadcast-id'));
        $mailing = $broadcast->getMailing();

        foreach ($usersId as $userId) {
            $user = $userRepository->find($userId);
            $contentMessage = $this->mailingContent->getContentMailing($mailing, $user, $broadcast);
            $this->mailing->sendMessage($contentMessage);

            $this->broadcastProvider->increment($broadcast->getId());

        }
        return parent::SUCCESS;
    }
}
