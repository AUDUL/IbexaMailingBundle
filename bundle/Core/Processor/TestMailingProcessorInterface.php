<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Processor;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;

interface TestMailingProcessorInterface
{
    public function execute(Mailing $mailing, string $testEmail): void;
}
