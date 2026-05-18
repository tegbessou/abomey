<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CreatePlayerFormData>
 */
final class CreatePlayerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'player.create.name_label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'player.create.name_placeholder',
                    'autofocus' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreatePlayerFormData::class,
            'translation_domain' => 'messages',
        ]);
    }
}
