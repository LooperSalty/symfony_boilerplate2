<?php

namespace App\Form\Product;

use App\Form\Product\Step\ProductDetailsStepType;
use App\Form\Product\Step\ProductLicenseStepType;
use App\Form\Product\Step\ProductLogisticsStepType;
use App\Form\Product\Step\ProductTypeStepType;

/**
 * Service qui gère la logique du formulaire multi-étapes pour les produits.
 */
class ProductFlowType
{
    public const STEP_TYPE = 1;
    public const STEP_DETAILS = 2;
    public const STEP_SPECIFIC = 3; // Logistics or License

    private const STEPS_PHYSICAL = [
        self::STEP_TYPE => [
            'form' => ProductTypeStepType::class,
            'title' => 'Type de produit',
        ],
        self::STEP_DETAILS => [
            'form' => ProductDetailsStepType::class,
            'title' => 'Détails du produit',
        ],
        self::STEP_SPECIFIC => [
            'form' => ProductLogisticsStepType::class,
            'title' => 'Informations logistiques',
        ],
    ];

    private const STEPS_DIGITAL = [
        self::STEP_TYPE => [
            'form' => ProductTypeStepType::class,
            'title' => 'Type de produit',
        ],
        self::STEP_DETAILS => [
            'form' => ProductDetailsStepType::class,
            'title' => 'Détails du produit',
        ],
        self::STEP_SPECIFIC => [
            'form' => ProductLicenseStepType::class,
            'title' => 'Informations de licence',
        ],
    ];

    /**
     * Retourne la configuration des étapes selon le type de produit.
     */
    public function getSteps(string $productType = 'physical'): array
    {
        return $productType === 'digital' ? self::STEPS_DIGITAL : self::STEPS_PHYSICAL;
    }

    /**
     * Retourne le nombre total d'étapes.
     */
    public function getTotalSteps(): int
    {
        return 3;
    }

    /**
     * Retourne la classe du formulaire pour une étape donnée.
     */
    public function getFormTypeForStep(int $step, string $productType = 'physical'): string
    {
        $steps = $this->getSteps($productType);
        return $steps[$step]['form'] ?? ProductTypeStepType::class;
    }

    /**
     * Retourne le titre de l'étape.
     */
    public function getStepTitle(int $step, string $productType = 'physical'): string
    {
        $steps = $this->getSteps($productType);
        return $steps[$step]['title'] ?? 'Étape inconnue';
    }

    /**
     * Vérifie si c'est la dernière étape.
     */
    public function isLastStep(int $step): bool
    {
        return $step >= self::STEP_SPECIFIC;
    }

    /**
     * Vérifie si c'est la première étape.
     */
    public function isFirstStep(int $step): bool
    {
        return $step <= self::STEP_TYPE;
    }

    /**
     * Retourne l'étape suivante.
     */
    public function getNextStep(int $currentStep): int
    {
        return min($currentStep + 1, self::STEP_SPECIFIC);
    }

    /**
     * Retourne l'étape précédente.
     */
    public function getPreviousStep(int $currentStep): int
    {
        return max($currentStep - 1, self::STEP_TYPE);
    }
}
