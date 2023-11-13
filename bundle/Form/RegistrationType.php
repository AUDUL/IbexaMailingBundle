<?php



declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Form;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Campaign as CampaignVoter;
use CodeRhapsodie\IbexaMailingBundle\Security\Voter\Mailing as MailingVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RegistrationType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('user', UserType::class);
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $allowedMailingList = [];
                $campaignRepository = $this->entityManager->getRepository(Campaign::class);
                // permissions on Campaing can be more complex, then we don't filter in SQL
                foreach ($campaignRepository->findAll() as $campaign) {
                    if ($this->authorizationChecker->isGranted(CampaignVoter::VIEW, $campaign)) {
                        foreach ($campaign->getMailingLists() as $mailingList) {
                            if ($this->authorizationChecker->isGranted(MailingVoter::VIEW, $mailingList)) {
                                $allowedMailingList[] = $mailingList;
                            }
                        }
                    }
                }

                $form
                    ->add(
                        'mailingLists',
                        EntityType::class,
                        [
                            'class' => MailingList::class,
                            'choices' => $allowedMailingList,
                            'expanded' => true,
                            'multiple' => true,
                            'required' => true,
                        ]
                    );
            }
        );
    }
}
