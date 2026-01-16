<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{
    public function testProductEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice(100.0);
        $product->setType('physical');

        $errors = $validator->validate($product);
        $this->assertCount(0, $errors);
    }

    public function testProductEntityIsInvalidWithoutName(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $product = new Product();
        $product->setPrice(100.0);
        $product->setType('physical');

        $errors = $validator->validate($product);
        $this->assertGreaterThan(0, count($errors));
    }
}
