<?php



declare(strict_types=1);

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Core\Tab;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Entity\Mailing as MailingEntity;
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
        return /* @Desc("Ibexa Mailing - Mailings Tab") */
            $this->translator->trans('mailings.tab.name', ['count' => count($this->mailings)], 'ibexamailing');
    }

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
