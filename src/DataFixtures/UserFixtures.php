<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Job;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $job1 = $manager->getRepository(Job::class)->findOneBy(['title' => 'Maçon']);
        $job2 = $manager->getRepository(Job::class)->findOneBy(['title' => 'Pompier']);
        $job3 = $manager->getRepository(Job::class)->findOneBy(['title' => 'Infirmier']);
        $job4 = $manager->getRepository(Job::class)->findOneBy(['title' => 'Médecin']);
        $job5 = $manager->getRepository(Job::class)->findOneBy(['title' => 'Soudeur']);

        $password = '$2y$13$a382JBn2YKNoovBEDA8I8.fYfss6JwrjyI11GMLGoRlmzRmW7L72q';

        $user = new User();
        $user->setFirstName('admin');
        $user->setLastName('admin');
        $user->setEmail("admin@user.fr");
        $user->setPhone("+33604444761");
        $user->setPassword($password);
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setGender("Monsieur");
        $user->setJobInteger($job1);
        $user->setBorn($faker->dateTimeBetween('-50 years', '-20 years'));
        $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->email());
            $user->setPhone('test');
            $user->setPassword($password);
            $user->setRoles(["ROLE_USER"]);
            $user->setGender($faker->randomElement(['Monsieur', 'Madame']));
            $user->setJobInteger($faker->randomElement([$job1, $job2, $job3, $job4, $job5]));
            $user->setBorn($faker->dateTimeBetween('-50 years', '-20 years'));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            JobFixtures::class,
        );
    }
}
