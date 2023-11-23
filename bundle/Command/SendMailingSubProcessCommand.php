<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Core\Mailer\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Core\Provider\Broadcast;
use CodeRhapsodie\IbexaMailingBundle\Core\Provider\MailingContent;
use CodeRhapsodie\IbexaMailingBundle\Repository\BroadcastRepository;
use CodeRhapsodie\IbexaMailingBundle\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ibexamailing:send:mailing-subprocess', hidden: true)]
class SendMailingSubProcessCommand extends Command
{
    public function __construct(
        private readonly BroadcastRepository $broadcastRepository,
        private readonly UserRepository $userRepository,
        private readonly MailingContent $mailingContent,
        private readonly Broadcast $broadcastProvider,
        private readonly Mailing $mailing
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
        $usersId = explode(',', $input->getOption('users-id'));
        /** @var \CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast $broadcast */
        $broadcast = $this->broadcastRepository->find($input->getOption('broadcast-id'));
        $mailing = $broadcast->getMailing();

        foreach ($usersId as $userId) {
            $user = $this->userRepository->find($userId);
            $contentMessage = $this->mailingContent->getContentMailing($mailing, $user, $broadcast);
            $this->mailing->sendMessage($contentMessage);

            $this->broadcastProvider->increment($broadcast->getId());
        }

        return parent::SUCCESS;
    }
}
