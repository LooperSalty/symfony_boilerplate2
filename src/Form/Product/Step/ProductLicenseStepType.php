<?php

namespace App\Form\Product\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductLicenseStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('licenseKey', TextType::class, [
                'label' => 'Clé de licence (modèle)',
                'required' => false,
                'data' => $options['data']['licenseKey'] ?? '',
                'attr' => [
                    'placeholder' => 'Ex: XXXX-XXXX-XXXX-XXXX',
                ],
            ])
            ->add('licenseType', ChoiceType::class, [
                'label' => 'Type de licence',
                'choices' => [
                    'Licence unique' => 'single',
                    'Licence multi-utilisateurs' => 'multi',
                    'Licence entreprise' => 'enterprise',
                ],
                'data' => $options['data']['licenseType'] ?? 'single',
            ])
            ->add('downloadLimit', IntegerType::class, [
                'label' => 'Limite de téléchargements',
                'required' => false,
                'data' => $options['data']['downloadLimit'] ?? 5,
                'attr' => [
                    'min' => 1,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
