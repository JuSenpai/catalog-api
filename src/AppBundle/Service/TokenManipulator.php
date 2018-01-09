<?php

namespace AppBundle\Service;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenManipulator
{
    private $manager;
    private $encoder;

    public function __construct(EntityManagerInterface $em, SHA256Encoder $encoder)
    {
        $this->manager = $em;
        $this->encoder = $encoder;
    }

    public function extract($token)
    {
        $decoded = base64_decode($token);
        preg_match('/(.+)_\+(\w{64})' . USER::TRUSTED_TOKEN_SALT . '/', $decoded, $matches);

        if (count($matches) !== 3) {
            return null;
        }

        $result = [
            "username" => $matches[1],
            "hash" => $matches[2]
        ];

        return $result;
    }

    public function isValid($token)
    {
        $tokenData = $this->extract($token);
        if ($tokenData === null) {
            return false;
        }

        $user = $this->manager->getRepository('AppBundle:User')->findOneBy(["username" => $tokenData['username']]);
        if ($user === null) {
            return false;
        }

        $seed = $user->getRole() . User::TOKEN_SALT . $user->getPasswordHash();
        if ($this->encoder->encode($seed) !== $tokenData["hash"]) {
            return false;
        }

        return true;
    }

    public function getUser($token)
    {
        $tokenData = $this->extract($token);
        if ($tokenData === null) {
            return null;
        } else return $this->manager->getRepository('AppBundle:User')->findOneBy(["username" => $tokenData["username"]]);
    }
}