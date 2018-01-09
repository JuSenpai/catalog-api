<?php

namespace AppBundle\Entity;

class Laboratory
{
    private $id;
    private $name;
    private $year;
    private $laborant;
    private $students;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLaborant()
    {
        return $this->laborant;
    }

    public function setLaborant($laborant)
    {
        $this->laborant = $laborant;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function setStudents($students)
    {
        $this->students = $students;
    }
}