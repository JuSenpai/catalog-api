<?php

namespace AppBundle\Entity;


class Student
{
    private $id;
    private $firstname;
    private $lastname;
    private $CNP;
    private $group;
    private $laboratories;

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

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function setLaboratories($laboratories)
    {
        $this->laboratories = $laboratories;
    }
}