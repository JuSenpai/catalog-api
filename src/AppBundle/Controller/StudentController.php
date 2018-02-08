<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Attendance;
use AppBundle\Entity\Laboratory;
use AppBundle\Entity\Student;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;

class StudentController extends FOSRestController
{
    public function getAllAction()
    {
        $result = [];
        $students = $this->getDoctrine()->getRepository('AppBundle:Student')->findAll();
        foreach ($students as $student) {
            $labs = [];
            foreach ($student->getLaboratories() as $lab) {
                $labs[] = array(
                    "id" => $lab->getId(),
                    "name" => $lab->getName(),
                    "year" => $lab->getYear(),
                    "count" => $lab->getCount(),
                    "laborant" => array(
                        "id" => $lab->getLaborant()->getId(),
                        "firstname" => $lab->getLaborant()->getFirstname(),
                        "lastname" => $lab->getLaborant()->getLastname(),
                        "CNP" => $lab->getLaborant()->getCNP(),
                    )
                );
            }

            $result[] = array(
                "id" => $student->getId(),
                "firstname" => $student->getFirstname(),
                "lastname" => $student->getLastname(),
                "CNP" => $student->getCNP(),
                "group" => $student->getGroup(),
                "laboratories" => $labs,
                "user" => array(
                    "username" => $student->getUser()->getUsername(),
                    "email" => $student->getUser()->getEmail(),
                    "role" => $student->getUser()->getRole(),
                ),
            );
        }
        return $this->handleView($this->view($result));
    }

    public function getOneAction(Student $student)
    {
        return $this->handleView($this->view([
            "id" => $student->getId(),
            "firstname" => $student->getFirstname(),
            "lastname" => $student->getLastname(),
            "CNP" => $student->getCNP(),
            "group" => $student->getGroup(),
            "laboratories" => $student->getLaboratories(),
            "user" => array(
                "username" => $student->getUser()->getUsername(),
                "email" => $student->getUser()->getEmail(),
                "role" => $student->getUser()->getRole(),
            ),
        ]));
    }

    public function addNewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $student = new Student();
                    $student->setFirstname($request->request->get('firstname'));
                    $student->setLastname($request->request->get('lastname'));
                    $student->setCNP($request->request->get('cnp'));
                    $student->setGroup($request->request->get('group'));
                    $labIds = $request->request->get('laboratories');
                    $laboratories = [];
                    $attendanceArray = [];
                    foreach ($labIds as $labId) {
                        $laboratories[$labId] = $em->getRepository('AppBundle:Laboratory')->find($labId);
                        $attendance = new Attendance();
                        $attendance->setStudent($student);
                        $attendance->setLaboratory($laboratories[$labId]);
                        $attendance->setAttendance(0);
                        $attendanceArray[] = $attendance;
                    }

                    $student->setAttendance($attendanceArray);
                    $student->setLaboratories($laboratories);
                    $username = strtolower($request->request->get('firstname'));
                    $username = preg_replace("/[\s\-]+/", "_", $username);
                    $username = $username . "." . preg_replace("/[\s\-]+/", "_", strtolower($request->request->get('lastname')));
                    $userData = [
                        "username" => $username,
                        "password" => $request->request->get('password'),
                        "email" => $request->request->get('email'),
                    ];
                    $student->setUser($this->get('catalog.users')->createUser($userData, "Student"));
                    $em->persist($student);
                    $em->flush();
                    $student = $em->getRepository('AppBundle:Student')->findOneBy(["CNP" => $request->request->get('cnp')]);
                    $labs = [];
                    foreach ($laboratories as $lab) {
                        $labs[] = array(
                            "id" => $lab->getId(),
                            "name" => $lab->getName(),
                            "year" => $lab->getYear(),
                            "count" => $lab->getCount(),
                            "laborant" => array(
                                "id" => $lab->getLaborant()->getId(),
                                "firstname" => $lab->getLaborant()->getFirstname(),
                                "lastname" => $lab->getLaborant()->getLastname(),
                                "CNP" => $lab->getLaborant()->getCNP(),
                            )
                        );
                    }
                    return $this->handleView($this->view(array(
                        "id" => $student->getId(),
                        "firstname" => $student->getFirstname(),
                        "lastname" => $student->getLastname(),
                        "group" => $student->getGroup(),
                        "CNP" => $student->getCNP(),
                        "laboratories" => $labs,
                        "user" => array(
                            "username" => $student->getUser()->getUsername(),
                            "email" => $student->getUser()->getEmail(),
                            "role" => $student->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch(Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function deleteAction(Request $request, int $student)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $student = $em->getRepository("AppBundle:Student")->find($student);
                    $em->remove($student->getUser());
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $student->getId(),
                        "firstname" => $student->getFirstname(),
                        "lastname" => $student->getLastname(),
                        "group" => $student->getGroup(),
                        "CNP" => $student->getCNP(),
                        "laboratories" => $student->getLaboratories(),
                        "user" => array(
                            "username" => $student->getUser()->getUsername(),
                            "email" => $student->getUser()->getEmail(),
                            "role" => $student->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch(Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function editAction(Request $request, int $student)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $student = $em->getRepository('AppBundle:Student')->find($student);
                    $student->setFirstname($request->request->get('firstname'));
                    $student->setLastname($request->request->get('lastname'));
                    $student->setCNP($request->request->get('cnp'));
                    $student->setGroup($request->request->get('group'));
                    $labs = [];
                    foreach ($student->getLaboratories() as $lab) {
                        $labs[] = array(
                            "id" => $lab->getId(),
                            "name" => $lab->getName(),
                            "year" => $lab->getYear(),
                            "count" => $lab->getCount(),
                            "laborant" => array(
                                "id" => $lab->getLaborant()->getId(),
                                "firstname" => $lab->getLaborant()->getFirstname(),
                                "lastname" => $lab->getLaborant()->getLastname(),
                                "CNP" => $lab->getLaborant()->getCNP(),
                            )
                        );
                    }
                    $student->setLaboratories($labs);
                    $em->persist($student);
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $student->getId(),
                        "firstname" => $student->getFirstname(),
                        "lastname" => $student->getLastname(),
                        "group" => $student->getGroup(),
                        "CNP" => $student->getCNP(),
                        "laboratories" => $labs,
                        "user" => array(
                            "username" => $student->getUser()->getUsername(),
                            "email" => $student->getUser()->getEmail(),
                            "role" => $student->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch(Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }
}