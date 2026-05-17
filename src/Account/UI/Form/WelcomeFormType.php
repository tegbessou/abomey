<?php

declare(strict_types=1);

namespace App\Account\UI\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<WelcomeFormData>
 */
final class WelcomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('accepted', CheckboxType::class, [
            'label' => 'welcome.consent_label',
            'label_translation_parameters' => ['%version%' => $options['version']],
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WelcomeFormData::class,
            'translation_domain' => 'messages',
        ]);
        $resolver->setRequired('version');
        $resolver->setAllowedTypes('version', 'string');
    }
}
