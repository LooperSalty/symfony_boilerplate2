<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\Product\ProductFlowType;
use App\Repository\ProductRepository;
use App\Security\Voter\ProductVoter;
use App\Service\ProductExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    private const SESSION_KEY = 'product_wizard_data';
    private const SESSION_STEP_KEY = 'product_wizard_step';
    private const SESSION_EDIT_ID = 'product_wizard_edit_id';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductFlowType $productFlow,
        private RequestStack $requestStack
    ) {
    }

    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::LIST);

        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'asc');

        $products = $productRepository->findAllSorted($sort, $direction);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    #[Route('/export', name: 'app_product_export', methods: ['GET'])]
    public function export(ProductExportService $exportService): StreamedResponse
    {
        $this->denyAccessUnlessGranted(ProductVoter::EXPORT);

        return $exportService->exportToCsv();
    }

    #[Route('/create', name: 'app_product_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::CREATE);

        $session = $this->requestStack->getSession();

        // Reset wizard if starting fresh
        if ($request->query->get('reset')) {
            $this->clearWizardSession($session);
            return $this->redirectToRoute('app_product_create');
        }

        // Get current step and data from session
        $currentStep = $session->get(self::SESSION_STEP_KEY, ProductFlowType::STEP_TYPE);
        $wizardData = $session->get(self::SESSION_KEY, []);

        // Determine product type for conditional step
        $productType = $wizardData['type'] ?? 'physical';

        // Get the form for current step
        $formType = $this->productFlow->getFormTypeForStep($currentStep, $productType);
        $form = $this->createForm($formType, $wizardData);
        $form->handleRequest($request);

        // Handle navigation buttons
        if ($request->isMethod('POST')) {
            // Previous button clicked
            if ($request->request->has('previous')) {
                $previousStep = $this->productFlow->getPreviousStep($currentStep);
                $session->set(self::SESSION_STEP_KEY, $previousStep);
                return $this->redirectToRoute('app_product_create');
            }

            // Next or Submit
            if ($form->isSubmitted() && $form->isValid()) {
                $stepData = $form->getData();
                $wizardData = array_merge($wizardData, $stepData);
                $session->set(self::SESSION_KEY, $wizardData);

                // Update product type if changed in step 1
                if ($currentStep === ProductFlowType::STEP_TYPE) {
                    $productType = $wizardData['type'] ?? 'physical';
                }

                // Check if last step
                if ($this->productFlow->isLastStep($currentStep)) {
                    // Create the product
                    $product = $this->createProductFromWizardData($wizardData);
                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    // Clear session
                    $this->clearWizardSession($session);

                    $this->addFlash('success', 'Produit créé avec succès.');
                    return $this->redirectToRoute('app_product_index');
                }

                // Move to next step
                $nextStep = $this->productFlow->getNextStep($currentStep);
                $session->set(self::SESSION_STEP_KEY, $nextStep);
                return $this->redirectToRoute('app_product_create');
            }
        }

        return $this->render('product/create.html.twig', [
            'form' => $form,
            'currentStep' => $currentStep,
            'totalSteps' => $this->productFlow->getTotalSteps(),
            'stepTitle' => $this->productFlow->getStepTitle($currentStep, $productType),
            'isFirstStep' => $this->productFlow->isFirstStep($currentStep),
            'isLastStep' => $this->productFlow->isLastStep($currentStep),
            'productType' => $productType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::EDIT, $product);

        $session = $this->requestStack->getSession();

        // Initialize wizard with product data
        $editId = $session->get(self::SESSION_EDIT_ID);
        if ($editId !== $product->getId() || $request->query->get('reset')) {
            $this->clearWizardSession($session);
            $session->set(self::SESSION_EDIT_ID, $product->getId());
            $session->set(self::SESSION_KEY, $this->getWizardDataFromProduct($product));
        }

        // Get current step and data from session
        $currentStep = $session->get(self::SESSION_STEP_KEY, ProductFlowType::STEP_TYPE);
        $wizardData = $session->get(self::SESSION_KEY, []);

        // Determine product type for conditional step
        $productType = $wizardData['type'] ?? $product->getType() ?? 'physical';

        // Get the form for current step
        $formType = $this->productFlow->getFormTypeForStep($currentStep, $productType);
        $form = $this->createForm($formType, $wizardData);
        $form->handleRequest($request);

        // Handle navigation buttons
        if ($request->isMethod('POST')) {
            // Previous button clicked
            if ($request->request->has('previous')) {
                $previousStep = $this->productFlow->getPreviousStep($currentStep);
                $session->set(self::SESSION_STEP_KEY, $previousStep);
                return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
            }

            // Next or Submit
            if ($form->isSubmitted() && $form->isValid()) {
                $stepData = $form->getData();
                $wizardData = array_merge($wizardData, $stepData);
                $session->set(self::SESSION_KEY, $wizardData);

                // Update product type if changed in step 1
                if ($currentStep === ProductFlowType::STEP_TYPE) {
                    $productType = $wizardData['type'] ?? 'physical';
                }

                // Check if last step
                if ($this->productFlow->isLastStep($currentStep)) {
                    // Update the product
                    $this->updateProductFromWizardData($product, $wizardData);
                    $this->entityManager->flush();

                    // Clear session
                    $this->clearWizardSession($session);

                    $this->addFlash('success', 'Produit modifié avec succès.');
                    return $this->redirectToRoute('app_product_index');
                }

                // Move to next step
                $nextStep = $this->productFlow->getNextStep($currentStep);
                $session->set(self::SESSION_STEP_KEY, $nextStep);
                return $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);
            }
        }

        return $this->render('product/create.html.twig', [
            'form' => $form,
            'currentStep' => $currentStep,
            'totalSteps' => $this->productFlow->getTotalSteps(),
            'stepTitle' => $this->productFlow->getStepTitle($currentStep, $productType),
            'isFirstStep' => $this->productFlow->isFirstStep($currentStep),
            'isLastStep' => $this->productFlow->isLastStep($currentStep),
            'productType' => $productType,
            'product' => $product,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::DELETE, $product);

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::VIEW, $product);

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    private function clearWizardSession($session): void
    {
        $session->remove(self::SESSION_KEY);
        $session->remove(self::SESSION_STEP_KEY);
        $session->remove(self::SESSION_EDIT_ID);
    }

    private function createProductFromWizardData(array $data): Product
    {
        $product = new Product();
        $this->updateProductFromWizardData($product, $data);
        return $product;
    }

    private function updateProductFromWizardData(Product $product, array $data): void
    {
        $product->setName($data['name'] ?? '');
        $product->setDescription($data['description'] ?? null);
        $product->setPrice((float)($data['price'] ?? 0));
        $product->setType($data['type'] ?? 'physical');
    }

    private function getWizardDataFromProduct(Product $product): array
    {
        return [
            'type' => $product->getType() ?? 'physical',
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
        ];
    }
}
