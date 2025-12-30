<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@agence.com');
        $admin->setFullName('Admin User');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Create regular user
        $user = new User();
        $user->setEmail('user@agence.com');
        $user->setFullName('John Doe');
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'user123');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Create sample services
        $serviceNames = [
            ['Paris City Tour', 'Découvrez les merveilles de Paris en 2 jours', 150.00],
            ['Croisière Méditerranée', 'Une croisière de luxe à travers la Méditerranée', 2500.00],
            ['Safari Kenya', 'Explorez la savane africaine avec nos guides experts', 3000.00],
            ['Séjour Bali', 'Plages paradisiaques et culture balinaise', 1200.00],
            ['Tokyo Express', 'Découvrez la capitale nippone en 5 jours', 1800.00],
        ];

        foreach ($serviceNames as [$title, $desc, $price]) {
            $service = new Service();
            $service->setTitle($title);
            $service->setDescription($desc);
            $service->setPrice((string) $price);
            $service->setAvailable(true);
            $manager->persist($service);
        }

        $manager->flush();
    }
}
