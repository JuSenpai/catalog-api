<?php

namespace AppBundle\Entity;

class Student
{
    private $id;
    private $firstname;
    private $lastname;
    private $CNP;
    private $group;
    private $user;
    private $attendance;
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

    public function setAttendance($attendance)
    {
        $this->attendance = $attendance;
    }

    public function getAttendance()
    {
        return $this->attendance;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getLaboratories()
    {
        return $this->laboratories;
    }

    public function setLaboratories($laboratories)
    {
        $this->laboratories = $laboratories;
    }
}