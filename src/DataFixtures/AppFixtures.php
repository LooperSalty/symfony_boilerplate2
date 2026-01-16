<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Manager
        $managerUser = new User();
        $managerUser->setEmail('manager@example.com');
        $managerUser->setFirstname('Manager');
        $managerUser->setLastname('Business');
        $managerUser->setRoles(['ROLE_MANAGER']);
        $managerUser->setPassword($this->hasher->hashPassword($managerUser, 'manager123'));
        $manager->persist($managerUser);

        // User
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        // Clients
        for ($i = 1; $i <= 10; $i++) {
            $client = new Client();
            $client->setFirstname('Client' . $i);
            $client->setLastname('Test');
            $client->setEmail('client' . $i . '@test.com');
            $client->setPhoneNumber('06000000' . sprintf('%02d', $i));
            $client->setAddress($i . ' Rue du Test, Paris');
            $manager->persist($client);
        }

        $manager->flush();
    }
}
