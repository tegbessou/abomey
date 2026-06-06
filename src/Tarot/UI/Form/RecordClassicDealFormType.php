<?php

declare(strict_types=1);

namespace App\Tarot\UI\Form;

use App\Tarot\Application\Shared\ParticipantSummaryView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $takerChoices = [];
        foreach ($participants as $participant) {
            $takerChoices[$participant->name] = $participant->id;
        }

        $builder
            ->add('takerId', ChoiceType::class, [
                'label' => 'deal.create.taker_label',
                'choices' => $takerChoices,
                'placeholder' => 'deal.create.taker_placeholder',
            ])
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
                ],
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
            'translation_domain' => 'messages',
        ]);
        $resolver->setAllowedTypes('participants', 'array');
    }
}
