<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Processor;

interface SendMailingProcessorInterface
{
    public function execute(\DateTime $overrideDatetime = null): void;
}
