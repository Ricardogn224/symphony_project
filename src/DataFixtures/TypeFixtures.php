<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Type;

class TypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $type1 = new Type();
        $type2 = new Type();
        $type3 = new Type();
        $type4 = new Type();

        $type1->setTitle('Article');
        $type2->setTitle('Vidéo');
        $type3->setTitle('Ebook');
        $type4->setTitle('Webinaire');

        $manager->persist($type1);
        $manager->persist($type2);
        $manager->persist($type3);
        $manager->persist($type4);

        $manager->flush();
    }
}