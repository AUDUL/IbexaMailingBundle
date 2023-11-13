<?php

namespace CodeRhapsodie\Bundle\IbexaMailingBundle\Form;

use CodeRhapsodie\Bundle\IbexaMailingBundle\Core\DataHandler\UserImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'required' => false,
            'label' => 'import.form.file',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => UserImport::class,
                'translation_domain' => 'ibexamailing',
            ]
        );
    }
}
