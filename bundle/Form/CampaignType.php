<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Form;

use CodeRhapsodie\IbexaMailingBundle\Entity\Campaign;
use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType
{
    /**
     * @var SiteaccessResolver
     */
    private $siteAccessResolver;

    public function __construct(SiteaccessResolver $siteAccessResolver)
    {
        $this->siteAccessResolver = $siteAccessResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $siteaccesses = array_combine(
            array_values($this->siteAccessResolver->getSiteaccesses()),
            array_values($this->siteAccessResolver->getSiteaccesses())
        );
        $builder
            ->add(
                'names',
                CollectionType::class,
                [
                    'label' => false,
                    'allow_add' => false,
                    'allow_delete' => false,
                    'entry_type' => TextType::class,
                    'required' => true,
                ]
            )
            ->add('senderName', TextType::class, ['required' => true, 'label' => 'campaign.form.sender_name'])
            ->add('senderEmail', EmailType::class, ['required' => true, 'label' => 'campaign.form.sender_email'])
            ->add('reportEmail', EmailType::class, ['required' => true, 'label' => 'campaign.form.report_email'])
            ->add(
                'returnPathEmail',
                EmailType::class,
                ['required' => false, 'label' => 'campaign.form.return_path_email']
            )
            ->add('locationId', HiddenType::class)
            ->add(
                'siteaccessLimit',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $siteaccesses,
                    'label' => 'campaign.form.siteaccess_limit',
                ]
            )
            ->add(
                'mailingLists',
                EntityType::class,
                [
                    'class' => MailingList::class,
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,
                    'label' => 'campaign.form.mailinglists',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Campaign::class,
                'translation_domain' => 'ibexamailing',
            ]
        );
    }
}
