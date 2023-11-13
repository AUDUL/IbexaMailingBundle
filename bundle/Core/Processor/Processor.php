<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Processor;

use Psr\Log\LoggerInterface;

abstract class Processor
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }
}
