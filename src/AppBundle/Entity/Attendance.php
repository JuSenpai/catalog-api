<?php

namespace AppBundle\Entity;


class Attendance
{
    private $id;
    private $student;
    private $laboratory;
    private $attendance;

    public function getStudent()
    {
        return $this->student;
    }

    public function setStudent($student)
    {
        $this->student = $student;
    }

    public function getLaboratory()
    {
        return $this->laboratory;
    }

    public function setLaboratory($laboratory)
    {
        $this->laboratory = $laboratory;
    }

    public function getAttendance()
    {
        return $this->attendance;
    }
    public function setAttendance($attendance)
    {
        $this->attendance = $attendance;
    }

    public function getId()
    {
        return $this->id;
    }
}