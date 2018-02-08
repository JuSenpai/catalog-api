<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Laborant;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class LaborantController extends FOSRestController
{
    public function getAllAction()
    {
        $laborants = $this->getDoctrine()->getManager()->getRepository('AppBundle:Laborant')->findAll();
        $response = $this->view($laborants);
        return $this->handleView($response);
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
                    $laborant = new Laborant();
                    $laborant->setFirstname($request->request->get('firstname'));
                    $laborant->setLastname($request->request->get('lastname'));
                    $laborant->setCNP($request->request->get('cnp'));
                    $username = strtolower($request->request->get('firstname'));
                    $username = preg_replace("/[\s\-]+/", "_", $username);
                    $username = $username . "." . preg_replace("/[\s\-]+/", "_", strtolower($request->request->get('lastname')));
                    $userData = [
                        "username" => $username,
                        "password" => $request->request->get('password'),
                        "email" => $request->request->get('email'),
                    ];
                    $laborant->setUser($this->get('catalog.users')->createUser($userData, "Laborant"));
                    $em->persist($laborant);
                    $em->flush();
                    $laborant = $em->getRepository('AppBundle:Laborant')->findOneBy(["CNP" => $request->request->get('cnp')]);
                    return $this->handleView($this->view(array(
                        "id" => $laborant->getId(),
                        "firstname" => $laborant->getFirstname(),
                        "lastname" => $laborant->getLastname(),
                        "CNP" => $laborant->getCNP(),
                        "user" => array(
                            "username" => $laborant->getUser()->getUsername(),
                            "email" => $laborant->getUser()->getEmail(),
                            "role" => $laborant->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch(Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function editAction(Request $request, Laborant $laborant)
    {
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $laborant->setFirstname($request->request->get('firstname'));
                    $laborant->setLastname($request->request->get('lastname'));
                    $laborant->setCNP($request->request->get('cnp'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($laborant);
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $laborant->getId(),
                        "firstname" => $laborant->getFirstname(),
                        "lastname" => $laborant->getLastname(),
                        "CNP" => $laborant->getCNP(),
                        "user" => array(
                            "username" => $laborant->getUser()->getUsername(),
                            "email" => $laborant->getUser()->getEmail(),
                            "role" => $laborant->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function deleteAction(Request $request, int $laborant)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $laborant = $em->getRepository("AppBundle:Laborant")->find($laborant);
                    $em->remove($laborant->getUser());
                    $em->flush();
                    return $this->handleView($this->view(array(
                        "id" => $laborant->getId(),
                        "firstname" => $laborant->getFirstname(),
                        "lastname" => $laborant->getLastname(),
                        "CNP" => $laborant->getCNP(),
                        "user" => array(
                            "username" => $laborant->getUser()->getUsername(),
                            "email" => $laborant->getUser()->getEmail(),
                            "role" => $laborant->getUser()->getRole()
                        ),
                    )));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch(Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function getOneAction(Laborant $laborant)
    {
        return $this->handleView($this->view($laborant));
    }
}