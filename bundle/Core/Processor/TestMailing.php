<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Processor;

use CodeRhapsodie\IbexaMailingBundle\Core\Mailer\Mailing as MailingMailer;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;

class TestMailing extends Processor implements TestMailingProcessorInterface
{
    /**
     * @var MailingMailer
     */
    private $mailingMailer;

    public function __construct(MailingMailer $mailingMailer)
    {
        $this->mailingMailer = $mailingMailer;
    }

    public function execute(Mailing $mailing, string $testEmail): void
    {
        $this->mailingMailer->sendMailing($mailing, $testEmail);
    }
}
