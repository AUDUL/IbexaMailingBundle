<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Tab;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign as CampaignEntity;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;

class Campaigns extends AbstractTab
{
    /**
     * @var CampaignEntity[]
     */
    private $campaigns;

    public function getIdentifier(): string
    {
        return 'ibexamailing-campaign-tab';
    }

    public function getName(): string
    {
        return /* @Desc("Ibexa Mailing - Campaigns Tab") */
            $this->translator->trans('campaigns.tab.name', ['count' => \count($this->campaigns)], 'ibexamailing');
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $parameters
     */
    public function renderView(array $parameters): string
    {
        return $this->twig->render(
            '@IbexaMailing/admin/tabs/campaigns.html.twig',
            [
                'items' => $this->campaigns,
            ]
        );
    }

    /**
     * Set the Campaigns.
     *
     * @param CampaignEntity[] $campaigns campaigns
     */
    public function setCampaigns(array $campaigns): self
    {
        $this->campaigns = $campaigns;

        return $this;
    }
}
