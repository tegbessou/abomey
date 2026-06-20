<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RecordClassicDealFormData>
 */
final class RecordClassicDealFormType extends AbstractType
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
            ->add('takerId', ChoiceType::class, [
                'label' => 'deal.create.taker_label',
                'choices' => $participantChoices,
                'expanded' => true,
                'multiple' => false,
            ]);

        if (5 === $mode) {
            $builder->add('partnerId', ChoiceType::class, [
                'label' => 'deal.create.partner_label',
                'choices' => $participantChoices,
                'placeholder' => 'deal.create.partner_alone',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ]);
        }

        $builder
            ->add('contract', ChoiceType::class, [
                'label' => 'deal.create.contract_label',
                'choices' => [
                    'deal.create.contract.garde' => 'garde',
                    'deal.create.contract.garde_sans' => 'garde_sans',
                    'deal.create.contract.garde_contre' => 'garde_contre',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('bouts', ChoiceType::class, [
                'label' => 'deal.create.bouts_label',
                'choices' => [
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('pointsScored', IntegerType::class, [
                'label' => 'deal.create.points_label',
                'attr' => [
                    'min' => 0,
                    'max' => 91,
                    'placeholder' => '0 à 91',
                    'inputmode' => 'numeric',
                    'class' => 'deal-form__points',
                ],
            ])
            ->add('petitAuBout', ChoiceType::class, [
                'label' => 'deal.create.petit_au_bout_label',
                'choices' => [
                    'deal.create.petit_au_bout.none' => 'none',
                    'deal.create.petit_au_bout.taker' => 'taker',
                    'deal.create.petit_au_bout.defense' => 'defense',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('chelem', ChoiceType::class, [
                'label' => 'deal.create.chelem_label',
                'choices' => [
                    'deal.create.chelem.none' => 'none',
                    'deal.create.chelem.realised' => 'realised',
                    'deal.create.chelem.announced_realised' => 'announced_realised',
                    'deal.create.chelem.announced_failed' => 'announced_failed',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('poignees', CollectionType::class, [
                'label' => 'deal.create.poignees_label',
                'entry_type' => PoigneeFormType::class,
                'entry_options' => [
                    'label' => false,
                    'participants' => $participants,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('miseres', CollectionType::class, [
                'label' => 'deal.create.miseres_label',
                'entry_type' => MisereFormType::class,
                'entry_options' => [
                    'label' => false,
                    'participants' => $participants,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'deal.create.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecordClassicDealFormData::class,
            'participants' => [],
            'mode' => 4,
            'translation_domain' => 'messages',
        ]);
        $resolver->setAllowedTypes('participants', 'array');
        $resolver->setAllowedTypes('mode', 'int');
    }
}
