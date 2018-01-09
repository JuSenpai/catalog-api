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
                    $em->persist($laboratory);
                    $em->flush();
                    return $this->handleView($this->view($laboratory));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
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
                    return $this->handleView($this->view($laboratory));
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
                    $laboratory->setLaborant($laborant);
                    $em->persist($laboratory);
                    $em->flush();
                    return $this->handleView($this->view($laboratory));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function getAllAction()
    {
        $laboratories = $this->getDoctrine()->getManager()->getRepository('AppBundle:Laboratory')->findAll();
        return $this->handleView($this->view($laboratories));
    }

    public function getOneAction(int $laboratory)
    {
        return $this->handleView($this->view(["id" => $laboratory]));
    }
}