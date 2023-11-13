<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Listener;

use CodeRhapsodie\IbexaMailingBundle\Core\Tab\Campaigns as CampaignsTab;
use CodeRhapsodie\IbexaMailingBundle\Core\Tab\Mailings as MailingsTab;
use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use Doctrine\ORM\EntityManagerInterface;
use Ibexa\AdminUi\Tab\Event\TabGroupEvent;
use Ibexa\AdminUi\Tab\TabRegistry;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class LocationViewGroupTab
{
    /**
     * @var TabRegistry
     */
    private $tabRegistry;

    /**
     * @var CampaignsTab
     */
    private $campaignsTab;

    /**
     * @var MailingsTab
     */
    private $mailingsTab;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        TabRegistry $tabRegistry,
        CampaignsTab $campaignsTab,
        MailingsTab $mailingsTab,
        EntityManagerInterface $entityManager
    ) {
        $this->tabRegistry = $tabRegistry;
        $this->campaignsTab = $campaignsTab;
        $this->mailingsTab = $mailingsTab;
        $this->entityManager = $entityManager;
    }

    public function onTabGroupPreRender(TabGroupEvent $event): void
    {
        $tabGroup = $event->getData();
        if ('location-view' !== $tabGroup->getIdentifier()) {
            return;
        }

        $parameters = $event->getParameters();
        /** @var Location $location */
        $location = $parameters['location'];

        $campaignRepo = $this->entityManager->getRepository(Campaign::class);
        $campaigns = $campaignRepo->findBy(['locationId' => $location->id]);
        if ($campaigns) {
            $this->campaignsTab->setCampaigns($campaigns);
            $this->tabRegistry->addTab($this->campaignsTab, 'location-view');
            $tabGroup->addTab($this->campaignsTab);
        }

        $mailingRepo = $this->entityManager->getRepository(Mailing::class);
        $mailings = $mailingRepo->findBy(['locationId' => $location->id]);
        if ($mailings) {
            $this->mailingsTab->setMailings($mailings);
            $this->tabRegistry->addTab($this->mailingsTab, 'location-view');
            $tabGroup->addTab($this->mailingsTab);
        }

        $event->setData($tabGroup);
    }
}
