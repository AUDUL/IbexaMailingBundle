<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Form;

use CodeRhapsodie\IbexaMailingBundle\Entity\MailingList;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingListType extends AbstractType
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
        $siteaccess = array_combine(
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
            ->add('withApproval', CheckboxType::class, [
                'required' => false,
                'label' => 'mailinglisttype.buildform.withapproval',
                ])
            ->add(
                'siteaccessLimit',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $siteaccess,
                    'label' => 'mailinglisttype.buildform.siteaccess_limit',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MailingList::class,
                'translation_domain' => 'ibexamailing',
            ]
        );
    }
}
