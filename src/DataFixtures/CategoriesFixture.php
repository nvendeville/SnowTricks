<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoriesFixture extends Fixture
{
    public const CATEGORY_REFERENCE = 'catégorie';

    public function load(ObjectManager $manager)
    {
        for ($count = 0; $count < 6; $count++) {
            $categoryFixture = new Category();
            $categoryFixture->setName('catégorie' . $count);
            $this->setReference(self::CATEGORY_REFERENCE . $count, $categoryFixture);

            $manager->persist($categoryFixture);
        }
        $manager->flush();
    }
}
