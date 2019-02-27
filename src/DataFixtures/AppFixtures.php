<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // Création de l'admin
        $admin = new User();
        $admin->setEmail('admin@admin.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->encoder->encodePassword($admin, 'admin'));
        $admin->setNom('Admin');
        $admin->setPrenom('Admin');
        $admin->setNomEntreprise('House of Code');
        $admin->setLogo('house_of_code.png');
        $admin->setAdresse('36 rue Michel Berthet');
        $admin->setCodePostal('69009');
        $admin->setTelephone('0665656565');
        $admin->setVille('Gorge de Loup');
        $admin->setSiteWeb('http://www.house-of-code.fr');
        $admin->setSocial('Twitter');
        $manager->persist($admin);
        $manager->flush();

        // Création des Entreprises
        $faker = Factory::create('fr_FR');
        $randomUser = [];
        for ($i = 0; $i < 10; $i++) {
        $user = new User();
        $user->setEmail($faker->email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->encoder->encodePassword($admin, 'password'));
        $user->setNom($faker->lastName);
        $user->setPrenom($faker->firstName);
        $user->setNomEntreprise($faker->company);
        //$user->setLogo($faker->imageUrl($width = 640, $height = 480));
        $user->setLogo($faker->image($width = 640, $height = 480));
        $user->setAdresse($faker->streetAddress);
        $user->setCodePostal($faker->postcode);
        $user->setVille($faker->city);
        $user->setTelephone($faker->phoneNumber);
        $user->setSiteWeb($faker->url);
        $user->setSocial($faker->url);
        $randomUser[] = $user;
        $manager->persist($user);
        }
        $manager->flush();


        for ($i = 0; $i < 10; $i++) {
            $employee = new Employee();
            $employee->setNom($faker->lastName);
            $employee->setPrenom($faker->firstName);
            $employee->setEmail($faker->email);
            $employee->setPoste($faker->jobTitle);
            $employee->setTelephone($faker->phoneNumber);
            $employee->setUser($faker->randomElement($randomUser));
            $manager->persist($employee);
        }
        $manager->flush();

    }

}
