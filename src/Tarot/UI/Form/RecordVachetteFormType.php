<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RecordVachetteFormData>
 */
final class RecordVachetteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var list<ParticipantSummaryView> $participants */
        $participants = $options['participants'];
        $mode = $options['mode'];

        $participantChoices = [];
        foreach ($participants as $participant) {
            $participantChoices[$participant->name] = $participant->id;
        }

        if (count($participants) > $mode) {
            $builder->add('deadPlayerIds', ChoiceType::class, [
                'label' => 'deal.create.dead_players_label',
                'choices' => $participantChoices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
        }

        $builder
            ->add('ranking', CollectionType::class, [
                'label' => 'deal.vachette.ranking_label',
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'choices' => $participantChoices,
                    'placeholder' => 'deal.vachette.position_placeholder',
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'deal.vachette.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecordVachetteFormData::class,
            'participants' => [],
            'mode' => 4,
            'translation_domain' => 'messages',
        ]);
        $resolver->setAllowedTypes('participants', 'array');
        $resolver->setAllowedTypes('mode', 'int');
    }
}
