<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Laboratory;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class LaboratoryController extends FOSRestController
{
    public function addNewAction(Request $request)
    {
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $em = $this->getDoctrine()->getManager();
                    $laboratory = new Laboratory();
                    $name = $request->request->get("name");
                    $year = $request->request->get("year");
                    $laborant = $em->getRepository('AppBundle:Laborant')->find($request->request->get('laborant'));
                    $laboratory->setName($name);
                    $laboratory->setYear($year);
                    $laboratory->setLaborant($laborant);
                    $laboratory->setCount($request->request->get('count'));
                    $em->persist($laboratory);
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $laboratory->getId(),
                        "name" => $laboratory->getName(),
                        "year" => $laboratory->getYear(),
                        "count" => $laboratory->getCount(),
                        "laborant" => [
                            "firstname" => $laboratory->getLaborant()->getFirstname(),
                            "lastname" => $laboratory->getLaborant()->getLastname(),
                            "CNP" => $laboratory->getLaborant()->getCNP(),
                        ],
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function currentUserAction(Request $request)
    {
        $cwt = $request->request->get('_CWT');
        $user = $this->get('security.token_manipulator')->getUser($cwt);
        $student = $this->getDoctrine()->getRepository('AppBundle:Student')->findOneBy(["user" => $user]);
        if ($student === null) {
            return $this->handleView($this->view(["code" => "404 - Not Found", "message" => "Ne pare rău, dar aparent, contul tău nu are niciun student asociat."]));
        }

        $laboratories = [];
        foreach ($student->getLaboratories() as $lab) {
            $laboratories[] = array(
                "id" => $lab->getId(),
                "name" => $lab->getName(),
                "year" => $lab->getYear(),
                "count" => $lab->getCount(),
                "laborant" => [
                    "id" => $lab->getLaborant()->getId(),
                    "firstname" => $lab->getLaborant()->getFirstname(),
                    "lastname" => $lab->getLaborant()->getLastname(),
                    "CNP" => $lab->getLaborant()->getCNP()
                ],
            );
        }

        return $this->handleView($this->view($laboratories));
    }

    public function deleteAction(Request $request, int $laboratory)
    {
        $em = $this->getDoctrine()->getManager();
        $laboratory = $em->getRepository('AppBundle:Laboratory')->find($laboratory);
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $em->remove($laboratory);
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $laboratory->getId(),
                        "name" => $laboratory->getName(),
                        "year" => $laboratory->getYear(),
                        "count" => $laboratory->getCount(),
                        "laborant" => [
                            "firstname" => $laboratory->getLaborant()->getFirstname(),
                            "lastname" => $laboratory->getLaborant()->getLastname(),
                            "CNP" => $laboratory->getLaborant()->getCNP(),
                        ],
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function editAction(Request $request, int $laboratory)
    {
        $em = $this->getDoctrine()->getManager();
        $laboratory = $em->getRepository('AppBundle:Laboratory')->find($laboratory);
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $laborant = $em->getRepository('AppBundle:Laborant')->find($request->request->get('laborant'));
                    $laboratory->setName($request->request->get('name'));
                    $laboratory->setYear($request->request->get('year'));
                    $laboratory->setCount($request->request->get('count'));
                    $laboratory->setLaborant($laborant);
                    $em->persist($laboratory);
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $laboratory->getId(),
                        "name" => $laboratory->getName(),
                        "year" => $laboratory->getYear(),
                        "count" => $laboratory->getCount(),
                        "laborant" => [
                            "firstname" => $laboratory->getLaborant()->getFirstname(),
                            "lastname" => $laboratory->getLaborant()->getLastname(),
                            "CNP" => $laboratory->getLaborant()->getCNP(),
                        ],
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function filterAction(Request $request)
    {
        $username = $request->get('u');

        $labs = $this->getDoctrine()->getManager()->getRepository('AppBundle:Laboratory')->findAll();
        $labs = array_filter($labs, function (Laboratory $lab) use ($username) {
            foreach ($lab->getStudents() as $student) {
                if ($username === $student->getUser()->getUsername()) {
                    return false;
                };
            }
            return true;
        });

        $result = [];
        /** @var Laboratory $laboratory */
        foreach ($labs as $laboratory) {
            $result[] = array(
                "id" => $laboratory->getId(),
                "name" => $laboratory->getName(),
                "year" => $laboratory->getYear(),
                "count" => $laboratory->getCount(),
                "laborant" => [
                    "firstname" => $laboratory->getLaborant()->getFirstname(),
                    "lastname" => $laboratory->getLaborant()->getLastname(),
                    "CNP" => $laboratory->getLaborant()->getCNP(),
                ],
            );
        }

        return $this->handleView($this->view($result));
    }

    public function getAllAction()
    {
        $laboratories = $this->getDoctrine()->getManager()->getRepository('AppBundle:Laboratory')->findAll();

        $result = [];
        /** @var Laboratory $laboratory */
        foreach ($laboratories as $laboratory) {
            $result[] = array(
                "id" => $laboratory->getId(),
                "name" => $laboratory->getName(),
                "year" => $laboratory->getYear(),
                "count" => $laboratory->getCount(),
                "laborant" => [
                    "firstname" => $laboratory->getLaborant()->getFirstname(),
                    "lastname" => $laboratory->getLaborant()->getLastname(),
                    "CNP" => $laboratory->getLaborant()->getCNP(),
                ],
            );
        }

        return $this->handleView($this->view($result));
    }

    public function getOneAction(int $laboratory)
    {
        return $this->handleView($this->view(["id" => $laboratory]));
    }
}