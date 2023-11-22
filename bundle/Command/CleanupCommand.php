<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Core\Registrar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ibexamailing:cleanup', description: 'Clean expired items')]
class CleanupCommand extends Command
{
    public function __construct(private readonly Registrar $registrar)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Remove the expired ConfirmationTokenRepository');
        $this->registrar->cleanup();
        $io->success('Done.');

        return Command::SUCCESS;
    }
}
