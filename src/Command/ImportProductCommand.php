<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:product:import',
    description: 'Import products from a CSV file',
)]
class ImportProductCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $io->error('File not found: ' . $filePath);
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $io->error('Cannot open file: ' . $filePath);
            return Command::FAILURE;
        }

        // Skip header
        fgetcsv($handle, 0, ';');

        $imported = 0;
        $errors = 0;
        $lineNumber = 1;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $lineNumber++;

            if (count($data) < 4) {
                $io->warning("Line $lineNumber: Not enough columns, skipping.");
                $errors++;
                continue;
            }

            $product = new Product();
            $product->setName($data[1] ?? '');
            $product->setDescription($data[2] ?? null);
            $product->setPrice((float)($data[3] ?? 0));
            $product->setType($data[4] ?? 'physical');

            $violations = $this->validator->validate($product);
            if (count($violations) > 0) {
                $io->warning("Line $lineNumber: Validation failed - " . $violations[0]->getMessage());
                $errors++;
                continue;
            }

            $this->entityManager->persist($product);
            $imported++;
        }

        fclose($handle);
        $this->entityManager->flush();

        $io->success("Import completed: $imported products imported, $errors errors.");

        return Command::SUCCESS;
    }
}
