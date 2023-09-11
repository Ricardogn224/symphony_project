<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Job;

class JobFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $job1 = new Job();
        $job2 = new Job();
        $job3 = new Job();
        $job4 = new Job();
        $job5 = new Job();
        $job6 = new Job();
        $job7 = new Job();
        $job8 = new Job();
        $job9 = new Job();
        $job10 = new Job();

        $job1->setTitle('Soudeur');
        $job1->setDescription('Soudeur de métaux');

        $job2->setTitle('Maçon');
        $job2->setDescription('Construction de bâtiments');

        $job3->setTitle('Pompier');
        $job3->setDescription('Pompier de la ville');

        $job4->setTitle('Médecin');
        $job4->setDescription('Médecin généraliste');

        $job5->setTitle('Travailleur agricole');
        $job5->setDescription('Travail sur des parcelles de terre');

        $job6->setTitle('Athlète de haut niveau');
        $job6->setDescription('Athlète de haut niveau notamment dans le domaine de la musculation (bodybuilding, haltérophilie, powerlifting, etc.)');

        $job7->setTitle('Infirmier');
        $job7->setDescription('Infirmier dans le domaine de la santé');

        $job8->setTitle('Professeur');
        $job8->setDescription('Professeur dans le domaine de l\'enseignement');

        $job9->setTitle('Avocat');
        $job9->setDescription('Avocat dans le domaine du droit');

        $job10->setTitle('Ingénieur');
        $job10->setDescription('Ingénieur dans le domaine de l\'informatique');

        $manager->persist($job1);
        $manager->persist($job2);
        $manager->persist($job3);
        $manager->persist($job4);
        $manager->persist($job5);
        $manager->persist($job6);
        $manager->persist($job7);
        $manager->persist($job8);
        $manager->persist($job9);
        $manager->persist($job10);

        $manager->flush();
    }
}