<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsersFixture extends Fixture
{
    public const USER_REFERENCE = 'user';

    public function load(ObjectManager $manager)
    {
        for ($count = 0; $count < 10; $count++) {
            $userFixture = new User();
            $userFixture->setEmail('pseudo' . $count . '@gmail.com');
            $userFixture->setPassword('pseudo' . $count . '!');
            $userFixture->setPseudo('pseudo' . $count);
            $userFixture->setAvatar('/img/avatar' . $count . '.jpg');

            $this->setReference(self::USER_REFERENCE . $count, $userFixture);

            $manager->persist($userFixture);
        }
            $manager->flush();
    }
}
