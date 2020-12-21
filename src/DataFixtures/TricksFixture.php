<?php

namespace App\DataFixtures;

use App\Entity\Tricks;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TricksFixture extends Fixture implements DependentFixtureInterface
{
    public const TRICK_REFERENCE = 'trick';

    public function load(ObjectManager $manager)
    {
        for ($count = 0; $count < 20; $count++) {
            $trickFixture = new Tricks();
            $trickFixture->setName("Trick " . $count);
            $trickFixture->setDescription("Description du trick" . $count);
            $trickFixture->setSlug("slug_" . $count);
            $trickFixture->setCreatedAt(new \DateTime());
            $trickFixture->setUpdatedAt(new \DateTime());
            $trickFixture->setCategory($this->getReference(CategoriesFixture::CATEGORY_REFERENCE . random_int (0, 5)));
            $trickFixture->setUser($this->getReference(UsersFixture::USER_REFERENCE . random_int (0, 9)));

            $this->setReference(self::TRICK_REFERENCE . $count, $trickFixture);

            $manager->persist($trickFixture);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return array(
            UsersFixture::class,
        );
    }
}
