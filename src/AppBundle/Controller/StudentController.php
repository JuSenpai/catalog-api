<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Student;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;

class StudentController extends FOSRestController
{
    public function getAllAction()
    {
        $students = $this->getDoctrine()->getRepository('AppBundle:Student')->findAll();
        return $this->handleView($this->view($students));
    }

    public function getOneAction(Student $student)
    {
        return $this->handleView($this->view($student));
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
                    return $this->handleView($this->view($request->request));
                    //$em->persist($student);
                    //$em->flush();
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function deleteAction(Request $request, int $student)
    {

    }

    public function editAction(Request $request, int $student)
    {

    }
}