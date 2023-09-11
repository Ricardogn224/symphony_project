<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Type;
use App\Entity\Content;

class ContentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $type1 = $manager->getRepository(Type::class)->findOneBy(['title' => 'Article']);

        $content1 = new Content();
        $content2 = new Content();
        $content3 = new Content();

        $content1->setTitle('Prévention du cancer du poumon');
        $content1->setDescription('Le cancer du poumon est une maladie grave et potentiellement mortelle, mais il existe des mesures que vous pouvez prendre pour réduire votre risque de développer cette maladie. Voici quelques mesures de prévention importantes à prendre en compte (arrêter de fumer, éviter les substances cancérigènes, etc.)');
        $content1->setTypeInteger($type1);

        $content2->setTitle('Prévention de l\'Hernie discale');
        $content2->setDescription('L\'hernie discale est une affection qui peut être douloureuse et invalidante, mais il existe des mesures que vous pouvez prendre pour réduire votre risque de développer cette condition. Voici quelques mesures de prévention importantes à prendre en compte (maintenir une bonne posture, éviter les mouvements brusques, etc.)');
        $content2->setTypeInteger($type1);

        $content3->setTitle('Prévention liée au rique du diabète');
        $content3->setDescription('Le diabète est une maladie grave et potentiellement mortelle, mais il existe des mesures que vous pouvez prendre pour réduire votre risque de développer cette maladie. Voici quelques mesures de prévention importantes à prendre en compte (maintenir un poids santé, faire de l\'exercice régulièrement, etc.)');
        $content3->setTypeInteger($type1);

        $manager->persist($content1);
        $manager->persist($content2);
        $manager->persist($content3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            TypeFixtures::class,
        );
    }
}