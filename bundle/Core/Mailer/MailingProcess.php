<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Mailer;

use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class MailingProcess
{
    private ?string $phpPath = null;

    public function __construct(private readonly LoggerInterface $logger, private readonly string $env, private readonly string $projectDir)
    {
    }

    public function runParallelProcess(
        int $broadcastId,
        \Generator $generator,
    ): void {
        /** @var \Symfony\Component\Process\Process[]|null[] */
        $processes = array_fill(0, $this->getNumberOfCPUCores() - 1, null);
        do {
            /** @var \Symfony\Component\Process\Process $process */
            foreach ($processes as $key => $process) {
                if ($process !== null && $process->isRunning()) {
                    continue;
                }

                if ($process !== null) {
                    // One of the processes just finished, so we increment progress bar

                    if (!$process->isSuccessful()) {
                        $this->logger->error(
                            sprintf(
                                'Child indexer process returned: %s - %s',
                                $process->getExitCodeText(),
                                $process->getErrorOutput()
                            )
                        );
                    }
                }

                if (!$generator->valid()) {
                    unset($processes[$key]);
                    continue;
                }

                $processes[$key] = $this->getPhpProcess($broadcastId, $generator->current());
                $processes[$key]->start();
                $generator->next();
            }

            if (!empty($processes)) {
                sleep(1);
            }
        } while (!empty($processes));
    }

    /**
     * @param array<int> $usersId
     */
    private function getPhpProcess(int $broadcastId, array $usersId): Process
    {
        if (empty($usersId)) {
            throw new InvalidArgumentException('--users-id', '$usersId cannot be empty');
        }

        $consolePath = file_exists(sprintf('%s/bin/console', $this->projectDir)) ? sprintf('%s/bin/console', $this->projectDir) : sprintf('%s/app/console', $this->projectDir);
        $subProcessArgs = [
            $this->getPhpPath(),
            $consolePath,
            'novaezmailing:send:mailing-subprocess',
            '--broadcast-id='.$broadcastId,
            '--users-id='.implode(',', $usersId),
            '--env='.$this->env,
        ];

        $process = new Process($subProcessArgs);
        $process->setTimeout(null);

        return $process;
    }

    private function getPhpPath(): string
    {
        if ($this->phpPath) {
            return $this->phpPath;
        }

        $phpFinder = new PhpExecutableFinder();
        $this->phpPath = $phpFinder->find();
        if (!$this->phpPath) {
            throw new \RuntimeException('The php executable could not be found. It is needed for executing parallel subprocesses, so add it to your PATH environment variable and try again');
        }

        return $this->phpPath;
    }

    /**
     * @return int
     */
    private function getNumberOfCPUCores()
    {
        $cores = 1;
        if (is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = \count($matches[0]);
        } elseif (\DIRECTORY_SEPARATOR === '\\') {
            // Windows
            if (($process = @popen('wmic cpu get NumberOfCores', 'rb')) !== false) {
                fgets($process);
                $cores = (int) fgets($process);
                pclose($process);
            }
        } elseif (($process = @popen('sysctl -a', 'rb')) !== false) {
            // *nix (Linux, BSD and Mac)
            $output = stream_get_contents($process);
            if (preg_match('/hw.ncpu: (\d+)/', $output, $matches)) {
                $cores = (int) $matches[1][0];
            }
            pclose($process);
        }

        return $cores;
    }
}
