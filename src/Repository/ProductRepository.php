<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find all products sorted by a specific field.
     *
     * @return Product[]
     */
    public function findAllSorted(string $sortField = 'id', string $direction = 'asc'): array
    {
        $allowedFields = ['id', 'name', 'price', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'id';
        }

        if (!in_array(strtolower($direction), $allowedDirections)) {
            $direction = 'asc';
        }

        return $this->createQueryBuilder('p')
            ->orderBy('p.' . $sortField, $direction)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all products sorted by price descending.
     *
     * @return Product[]
     */
    public function findAllByPriceDesc(): array
    {
        return $this->findAllSorted('price', 'desc');
    }
}
