<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\Form;

use CodeRhapsodie\IbexaMailingBundle\Entity\Mailing;
use CodeRhapsodie\IbexaMailingBundle\Validator\Constraints\Location as LocationConstraint;
use CodeRhapsodie\IbexaMailingBundle\Validator\Constraints\Names as NamesConstraint;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingType extends AbstractType
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
        $fromArray = function ($array) {
            return implode(',', $array);
        };
        $fromString = function ($string) {
            return array_unique($string !== null ? explode(',', $string) : []);
        };

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
                    'constraints' => [new NamesConstraint()],
                ]
            )
            ->add('subject', TextType::class, ['required' => false, 'label' => 'form.subject'])
            ->add('recurring', CheckboxType::class, ['label' => 'mailing.buildform.recuring_mailing'])
            ->add(
                'locationId',
                HiddenType::class,
                [
                    'required' => true,
                    'constraints' => [new LocationConstraint()],
                ]
            )
            ->add(
                'hoursOfDay',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'generic.details.hours_day',
                ]
            )
            ->add('daysOfWeek', TextType::class, ['label' => 'generic.details.days_week'])
            ->add('daysOfMonth', TextType::class, ['label' => 'generic.details.days_month'])
            ->add('daysOfYear', TextType::class, ['label' => 'generic.details.days_year'])
            ->add('weeksOfMonth', TextType::class, ['label' => 'generic.details.weeks_month'])
            ->add('monthsOfYear', TextType::class, ['label' => 'generic.details.months_year'])
            ->add('weeksOfYear', TextType::class, ['label' => 'generic.details.weeks_year']);

        $transformationFields = [
            'hoursOfDay',
            'daysOfWeek',
            'daysOfMonth',
            'daysOfYear',
            'weeksOfMonth',
            'monthsOfYear',
            'weeksOfYear',
        ];

        foreach ($transformationFields as $field) {
            $builder->get($field)->addModelTransformer(new CallbackTransformer($fromArray, $fromString));
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($siteaccesses) {
                $form = $event->getForm();
                $mailing = $event->getData();
                $siteaccessLimit = $mailing->getCampaign()->getSiteaccessLimit() ?? [];
                $siteaccessLimit = array_combine(
                    array_values($siteaccessLimit),
                    array_values($siteaccessLimit)
                );
                /* @var Mailing $mailing */
                $form->add(
                    'siteAccess',
                    ChoiceType::class,
                    [
                        'label' => 'mailing.buildform.which_siteaccess',
                        'choices' => \count($siteaccessLimit) > 0 ? $siteaccessLimit : $siteaccesses,
                        'expanded' => true,
                        'multiple' => false,
                        'required' => true,
                    ]
                );
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Mailing::class,
                'translation_domain' => 'ibexamailing',
            ]
        );
    }
}
