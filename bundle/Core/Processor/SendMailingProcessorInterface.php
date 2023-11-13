<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Processor;

use DateTime;

interface SendMailingProcessorInterface
{
    public function execute(?DateTime $overrideDatetime = null): void;
}
