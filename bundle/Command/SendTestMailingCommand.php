<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Core\Processor\TestMailingProcessorInterface as TestMailing;
use CodeRhapsodie\IbexaMailingBundle\Repository\MailingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ibexamailing:test:send:mailing', description: 'Send a mailing to an specific email', hidden: true)]
class SendTestMailingCommand extends Command
{
    public function __construct(private readonly TestMailing $processor, private readonly MailingRepository $mailingRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mailingId', InputArgument::REQUIRED, 'The Mailing Id')
            ->addArgument('recipient', InputArgument::REQUIRED, "The recipient's email address");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mailingId = (int) $input->getArgument('mailingId');
        $recipientEmail = $input->getArgument('recipient');
        $io->title('Sending a Mailing for test');
        $io->writeln("MailingRepository ID: <comment>{$mailingId}</comment>");
        $io->writeln("To: <comment>{$recipientEmail}</comment>");
        $mailing = $this->mailingRepository->find($mailingId);
        $this->processor->execute($mailing, $recipientEmail);
        $io->success('Done.');

        return Command::SUCCESS;
    }
}
