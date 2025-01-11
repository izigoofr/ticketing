<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $password = 'root';
        $dateOfBirth = new \DateTime('1990-01-01');
        // Format the DateTime object as a string
        $formattedDateOfBirth = $dateOfBirth->format('Y-m-d');

        $user->setAddress('Route de Cabrières d Aigues')
            ->setCountry('La Tour-d Aigues')
            ->setDateOfBirth($formattedDateOfBirth)
            ->setEmail('admin@florajet.com')
            ->setFirstName('root')
            ->setRoles(["ROLE_ADMIN"])
            ->setLastName('root')
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->setPhoneNumber('0615225879')
            ->setState('France')
            ->setZipCode('84240');

        $manager->persist($user);

        for ($i = 1; $i <= 8; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'root')); // Example hashed password
            $user->setFirstName("User$i");
            $user->setLastName("Doe");
            $user->setAddress("Route de Cabrières d Aigues");
            $user->setDateOfBirth("1990-01-01");
            $user->setPhoneNumber("0490070713");
            $user->setCountry("France");
            $user->setState("La Tour-d Aigues");
            $user->setZipCode("84240");

            $manager->persist($user);
        }
        
        $manager->flush();
    }
}
