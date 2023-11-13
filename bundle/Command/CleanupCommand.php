<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use CodeRhapsodie\IbexaMailingBundle\Core\Registrar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanupCommand extends Command
{
    /**
     * @var Registrar
     */
    private $registrar;

    public function __construct(Registrar $registrar)
    {
        parent::__construct();
        $this->registrar = $registrar;
    }

    protected function configure(): void
    {
        $this
            ->setName('ibexamailing:cleanup')
            ->setDescription('Clean expired items');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Remove the expired ConfirmationToken');
        $this->registrar->cleanup();
        $io->success('Done.');

        return Command::SUCCESS;
    }
}
