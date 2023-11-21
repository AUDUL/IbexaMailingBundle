<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Core\Tab;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing as MailingEntity;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;

class Mailings extends AbstractTab
{
    /**
     * @var MailingEntity[]
     */
    private $mailings;

    public function getIdentifier(): string
    {
        return 'ibexamailing-mailings-tab';
    }

    public function getName(): string
    {
        return /* @Desc("Ibexa MailingRepository - Mailings Tab") */
            $this->translator->trans('mailings.tab.name', ['count' => \count($this->mailings)], 'ibexamailing');
    }

    /**
     * {@inheritdoc}
     *
     * @param array<mixed> $parameters
     */
    public function renderView(array $parameters): string
    {
        return $this->twig->render(
            '@IbexaMailing/admin/tabs/mailings.html.twig',
            [
                'items' => $this->mailings,
            ]
        );
    }

    /**
     * @param MailingEntity[] $mailings
     */
    public function setMailings(array $mailings): self
    {
        $this->mailings = $mailings;

        return $this;
    }
}
