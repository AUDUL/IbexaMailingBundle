<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Processor;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Mailer\Mailing as MailingMailer;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;

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
