<?php

namespace AppBundle\Entity;

class Laborant
{
    private $id;
    private $firstname;
    private $lastname;
    private $CNP;
    private $user;
    private $laboratories = [];

    public function getId()
    {
        return $this->id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getCNP()
    {
        return $this->CNP;
    }

    public function setCNP($CNP)
    {
        $this->CNP = $CNP;
    }

    public function setLaboratories($laboratories)
    {
        $this->laboratories = $laboratories;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}