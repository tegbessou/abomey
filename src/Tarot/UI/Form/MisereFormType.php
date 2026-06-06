<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array{announcerId: string, type: string}>
 */
final class MisereFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var list<ParticipantSummaryView> $participants */
        $participants = $options['participants'];

        $announcerChoices = [];
        foreach ($participants as $participant) {
            $announcerChoices[$participant->name] = $participant->id;
        }

        $builder
            ->add('announcerId', ChoiceType::class, [
                'label' => 'deal.create.misere.announcer_label',
                'choices' => $announcerChoices,
                'placeholder' => 'deal.create.misere.announcer_placeholder',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'deal.create.misere.type_label',
                'choices' => [
                    'deal.create.misere.type.atouts' => 'atouts',
                    'deal.create.misere.type.tete' => 'tete',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'participants' => [],
            'translation_domain' => 'messages',
        ]);
        $resolver->setAllowedTypes('participants', 'array');
    }
}
