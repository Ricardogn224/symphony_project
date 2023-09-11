<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Vaccine;

class VaccineFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $vaccine1 = new Vaccine();
        $vaccine2 = new Vaccine();
        $vaccine3 = new Vaccine();
        $vaccine4 = new Vaccine();
        $vaccine5 = new Vaccine();
        $vaccine6 = new Vaccine();

        $vaccine1->setTitle('papillomavirus');
        $vaccine1->setDescription('vaccin contre le papillomavirus utile pour la prévention d\' un cancer du poumon ou du col de l\'utérus');
        $vaccine1->setRecommandationAge(12);
 
        $vaccine2->setTitle('pneumocoque');
        $vaccine2->setDescription('vaccin contre le pneumocoque utile pour la prévention d\' un cancer du poumon');
        $vaccine2->setRecommandationAge(65);

        $vaccine3->setTitle('hépatite B');
        $vaccine3->setDescription('vaccin contre l\'hépatite B utile pour la prévention d\' un cancer du foie');
        $vaccine3->setRecommandationAge(18);

        $vaccine4->setTitle('hépatite A');
        $vaccine4->setDescription('vaccin contre l\'hépatite A utile pour la prévention d\' un cancer du foie');
        $vaccine4->setRecommandationAge(18);

        $vaccine5->setTitle('grippe');
        $vaccine5->setDescription('vaccin contre la grippe utile pour la prévention d\' un cancer du poumon');
        $vaccine5->setRecommandationAge(65);

        $vaccine6->setTitle('covid-19');
        $vaccine6->setDescription('vaccin contre le Covid-19 utile pour la prévention d\' un cancer du poumon');
        $vaccine6->setRecommandationAge(65);

        $manager->persist($vaccine1);
        $manager->persist($vaccine2);
        $manager->persist($vaccine3);
        $manager->persist($vaccine4);
        $manager->persist($vaccine5);
        $manager->persist($vaccine6);

        $manager->flush();
    }
}