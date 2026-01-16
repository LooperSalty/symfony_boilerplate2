<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportService
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function exportToCsv(): StreamedResponse
    {
        $products = $this->productRepository->findAll();

        $response = new StreamedResponse(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, ['ID', 'Name', 'Description', 'Price', 'Type'], ';');

            // Data rows
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->getId(),
                    $product->getName(),
                    $product->getDescription() ?? '',
                    $product->getPrice(),
                    $product->getType(),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="products_export_' . date('Y-m-d_His') . '.csv"');

        return $response;
    }
}
