<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $em;
    private $encoder;
    private $hierarchy;

    public function __construct(EntityManagerInterface $em, SHA256Encoder $encoder, RoleHierarchy $hierarchy)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->hierarchy = $hierarchy;
    }

    /**
     * @param array $userData
     * @param $role
     * @return User
     *
     * Adds a user to the application
     * This is not used to register users to the systems;
     * Users created using this function do not have a corresponding student/laborant entity
     * This function ignores username collision; so take extra care
     *
     */
    public function createUser(array $userData, $role = "")
    {
        $role = $role === "" ? $userData['role'] : $role;

        $salt = $this->encoder->generateSalt(6);
        $passwordHash = $this->encoder->encode($userData['password'], $salt);

        $user = new User();
        $user->setUsername($userData['username']);
        $user->setPasswordHash($passwordHash);
        $user->setSalt($salt);
        $user->setEmail($userData['email']);
        $user->setRole($role);
        $user->setWebToken($user->getWebToken());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}