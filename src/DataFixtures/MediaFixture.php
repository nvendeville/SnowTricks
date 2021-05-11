<?php

namespace App\DataFixtures;

use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MediaFixture extends Fixture implements DependentFixtureInterface
{
    public const MEDIA_REFERENCE = 'media';

    public function load(ObjectManager $manager)
    {
        for ($count = 0; $count < 24; $count++) {
            $mediaFixture = new Media();
            $mediaFixture->setFeaturedImg(0);
            $mediaFixture->setLink('trick' . $count . '.jpg');
            $mediaFixture->setType('image');
            $mediaFixture->setTrick($this->getReference(TricksFixture::TRICK_REFERENCE . random_int(0, 6)));

            $this->setReference(self::MEDIA_REFERENCE . $count, $mediaFixture);

            $manager->persist($mediaFixture);
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
