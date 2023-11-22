<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Provider;

use CodeRhapsodie\IbexaMailingBundle\Core\Modifier\ModifierInterface;
use CodeRhapsodie\IbexaMailingBundle\Entity\Broadcast as BroadcastEntity;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Entity\User as UserEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MailingContent
{
    /**
     * @var array<int, string>
     */
    protected $nativeContent;

    /**
     * MailingContent constructor.
     *
     * @param ModifierInterface[] $modifiers
     */
    public function __construct(protected readonly iterable $modifiers, protected readonly RouterInterface $router, private readonly KernelInterface $kernel)
    {
    }

    public function preFetchContent(Mailing $mailing): string
    {
        $client = new HttpKernelBrowser($this->kernel);
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

    public function getContentMailing(
        Mailing $mailing,
        UserEntity $recipient,
        BroadcastEntity $broadcast
    ): Email {
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
        if (!empty($campaign->getReturnPathEmail())) {
            $message->returnPath($campaign->getReturnPathEmail());
        }

        return $message;
    }

    public function getNativeContent(Mailing $mailing): string
    {
        if (!isset($this->nativeContent[$mailing->getLocationId()])) {
            $this->preFetchContent($mailing);
        }

        return $this->nativeContent[$mailing->getLocationId()];
    }
}
