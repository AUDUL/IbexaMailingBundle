<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Processor;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;

interface TestMailingProcessorInterface
{
    public function execute(Mailing $mailing, string $testEmail): void;
}
