<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Provider;

use App\Kernel;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Modifier\ModifierInterface;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Broadcast as BroadcastEntity;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\User as UserEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MailingContent
{
    /**
     * @var array
     */
    protected $nativeContent;

    /**
     * @var ModifierInterface[]
     */
    protected $modifiers;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * MailingContent constructor.
     *
     * @param ModifierInterface[] $modifiers
     */
    public function __construct(iterable $modifiers, RouterInterface $router, private readonly string $kernelEnv)
    {
        $this->modifiers = $modifiers;
        $this->router = $router;
    }

    public function preFetchContent(Mailing $mailing): string
    {
        $kernel = new Kernel($this->kernelEnv, false);
        $client = new HttpKernelBrowser($kernel);
        $url = $this->router->generate(
            '_ibexamailing_ez_content_view',
            [
                'locationId' => $mailing->getLocation()->id,
                'contentId' => $mailing->getContent()->id,
                'mailingId' => $mailing->getId(),
                'siteaccess' => $mailing->getSiteAccess(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $crawler = $client->request('/GET', $url);
        $this->nativeContent[$mailing->getLocationId()] = "<!DOCTYPE html><html>{$crawler->html()}</html>";

        return $this->nativeContent[$mailing->getLocationId()];
    }

    private function getNativeContent(Mailing $mailing): string
    {
        if (!isset($this->nativeContent[$mailing->getLocationId()])) {
            $this->preFetchContent($mailing);
        }

        return $this->nativeContent[$mailing->getLocationId()];
    }

    public function getContentMailing(
        Mailing         $mailing,
        UserEntity      $recipient,
        BroadcastEntity $broadcast
    ): Email
    {
        $html = $this->getNativeContent($mailing);
        foreach ($this->modifiers as $modifier) {
            $html = $modifier->modify($mailing, $recipient, $html, ['broadcast' => $broadcast]);
        }
        $message = new TemplatedEmail();
        $message->subject($mailing->getSubject());
        $message->html($html);
        $campaign = $mailing->getCampaign();
        $message->from(new Address($campaign->getSenderEmail(), $campaign->getSenderName()));
        $message->to($recipient->getEmail());
        $message->bcc($campaign->getReportEmail());
        if (!empty($campaign->getReturnPathEmail())) {
            $message->returnPath($campaign->getReturnPathEmail());
        }

        return $message;
    }
}
