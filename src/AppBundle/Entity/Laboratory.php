<?php

namespace AppBundle\Entity;

class Laboratory
{
    private $id;
    private $name;
    private $year;
    private $count;
    private $laborant;
    private $students;
    private $attendance;

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

    public function setAttendance($attendance)
    {
        $this->attendance = $attendance;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getStudents()
    {
        return $this->students;
    }

    public function setStudents($students)
    {
        $this->students = $students;
    }
}