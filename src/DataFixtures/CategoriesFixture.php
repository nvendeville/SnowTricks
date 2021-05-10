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
        $categoryArrays = [
            'flip',
            'grab',
            'one foot trick',
            'rotation',
            'slide'
        ];

        foreach ($categoryArrays as $categoryArray) {
            $categoryFixture = new Category();
            $categoryFixture->setName($categoryArray);

            $this->setReference(self::CATEGORY_REFERENCE . $categoryArray, $categoryFixture);

            $manager->persist($categoryFixture);
        }
        $manager->flush();
    }
}
