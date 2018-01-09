<?php

namespace AppBundle\Entity;

class User
{
    const TOKEN_SALT = "this aint some salty salt";
    const TRUSTED_TOKEN_SALT = " this token is to be trusted";
    private $id;
    private $username;
    private $passwordHash;
    private $email;
    private $salt;
    private $webToken;
    private $role;

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getWebToken()
    {
        $hash = hash('sha256', $this->role . self::TOKEN_SALT . $this->passwordHash);
        return base64_encode($this->username . '_+' . $hash . self::TRUSTED_TOKEN_SALT);
    }

    public function setWebToken($webToken)
    {
        $this->webToken = $webToken;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
}