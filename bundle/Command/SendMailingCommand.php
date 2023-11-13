<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Command;

use Carbon\Carbon;
use CodeRhapsodie\IbexaMailingBundle\Core\Processor\SendMailingProcessorInterface as SendMailing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMailingCommand extends Command
{
    /**
     * @var SendMailing
     */
    private $processor;

    public function __construct(SendMailing $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setName('ibexamailing:send:mailing')
            ->addOption(
                'overrideDatetime',
                'o',
                InputOption::VALUE_REQUIRED,
                'Override the current Datetime <comment>2018-12-05 16:42</comment>'
            )
            ->setDescription('Send all the mailings according to their sending rules.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Process the mailings');
        $overrideDatetime = null;
        if ($input->getOption('overrideDatetime')) {
            $overrideDatetime = Carbon::createFromFormat('Y-m-d H:i', $input->getOption('overrideDatetime'));
            $io->comment('Using an override date: <comment>' . $overrideDatetime->format('Y-m-d H:i') . '</comment>');
        }
        $this->processor->execute($overrideDatetime);
        $io->success('Done.');

        return Command::SUCCESS;
    }
}
