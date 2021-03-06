<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentsFixture extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        for ($count = 0; $count < 30; $count++) {
            $commentFixture = new Comment();
            $commentFixture->setDescription("Commentaire fictif " . $count);
            $commentFixture->setCreatedAt();
            $commentFixture->setUpdatedAt();
            $commentFixture->setUser($this->getReference(UsersFixture::USER_REFERENCE . random_int(0, 9)));
            $commentFixture->setTrick($this->getReference(TricksFixture::TRICK_REFERENCE . random_int(0, 7)));

            $manager->persist($commentFixture);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return array(
            TricksFixture::class,
        );
    }
}
