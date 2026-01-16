<?php

namespace App\Form\Product\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductLogisticsStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'data' => $options['data']['weight'] ?? null,
                'scale' => 2,
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensions (L x l x H en cm)',
                'required' => false,
                'data' => $options['data']['dimensions'] ?? '',
                'attr' => [
                    'placeholder' => 'Ex: 30 x 20 x 10',
                ],
            ])
            ->add('stock', NumberType::class, [
                'label' => 'Stock disponible',
                'required' => false,
                'data' => $options['data']['stock'] ?? 0,
                'html5' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
