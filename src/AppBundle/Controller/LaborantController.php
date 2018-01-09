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
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $firstname = $request->request->get('firstname');
                    $lastname = $request->request->get('lastname');
                    $cnp = $request->request->get('cnp');
                    $laborant = new Laborant();
                    $laborant->setFirstname($firstname);
                    $laborant->setLastname($lastname);
                    $laborant->setCNP($cnp);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($laborant);
                    $em->flush();
                    return $this->handleView($this->view($laborant));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
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
                    return $this->handleView($this->view($laborant));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function deleteAction(Request $request, Laborant $laborant)
    {
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $user = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($user, $hierarchy->get("administrator"))) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($laborant);
                    $em->flush();
                    return $this->handleView($this->view($laborant));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function getOneAction(Laborant $laborant)
    {
        return $this->handleView($this->view($laborant));
    }
}