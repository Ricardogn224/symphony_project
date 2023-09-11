<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Pathology;

class PathologyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $pathology1 = new Pathology();
        $pathology2 = new Pathology();
        $pathology3 = new Pathology();
        $pathology4 = new Pathology();
        $pathology5 = new Pathology();
        $pathology6 = new Pathology();

        $pathology1->setTitle('Cancer du poumon');
        $pathology1->setDescription('Douleur au niveau du poumon en particulier au niveau du thorax');

        $pathology2->setTitle('Hernie discale');
        $pathology2->setDescription('Douleur au niveau du dos');

        $pathology3->setTitle('Diabète');
        $pathology3->setDescription('Douleur au niveau des jambes en particulier au niveau des pieds');

        $pathology4->setTitle('Dépression chronique');
        $pathology4->setDescription('Troubles du sommeil, anxiété, perte d\'appétit');

        $pathology5->setTitle('Cancer du colon');
        $pathology5->setDescription('Douleur au niveau du ventre');
    
        $pathology6->setTitle('Arthrose');
        $pathology6->setDescription('Douleur au niveau des articulations');

        $manager->persist($pathology1);
        $manager->persist($pathology2);
        $manager->persist($pathology3);
        $manager->persist($pathology4);
        $manager->persist($pathology5);
        $manager->persist($pathology6);

        $manager->flush();
    }
}