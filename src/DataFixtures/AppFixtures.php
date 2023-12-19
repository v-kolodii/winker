<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('winker_admin@gmail.com');

        $password = $this->hasher->hashPassword($user, 'Winker2023@DevTeam');
        $user->setPassword($password);
        $user->setRoles([User::ROLE_SUPER_ADMIN]);

        $manager->persist($user);
        $manager->flush();
    }
}
