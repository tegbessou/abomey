<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use App\Tarot\Application\ListMyPlayers\PlayerView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CreateGameFormData>
 */
final class CreateGameFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var list<PlayerView> $players */
        $players = $options['players'];

        $playerChoices = [];
        foreach ($players as $player) {
            $playerChoices[$player->name] = $player->id;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'game.create.name_label',
                'required' => true,
                'attr' => [
                    'placeholder' => 'game.create.name_placeholder',
                    'autofocus' => true,
                ],
            ])
            ->add('mode', ChoiceType::class, [
                'label' => 'game.create.mode_label',
                'choices' => [
                    'game.mode.3' => 3,
                    'game.mode.4' => 4,
                    'game.mode.5' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'placeholder' => false,
            ])
            ->add('participants', ChoiceType::class, [
                'label' => 'game.create.participants_label',
                'choices' => $playerChoices,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateGameFormData::class,
            'players' => [],
            'translation_domain' => 'messages',
        ]);
        $resolver->setAllowedTypes('players', 'array');
    }
}
