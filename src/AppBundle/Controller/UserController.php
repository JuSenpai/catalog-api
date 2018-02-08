<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Laborant;
use AppBundle\Entity\Student;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends FOSRestController
{
    public function validateAuthAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(['username' => $username]);
        if ($user === null) {
            $result = ["username_error" => "Utilizatorul căutat nu există."];
            $view = $this->view($result);
            return $this->handleView($view);
        }

        $encoder = $this->get('security.string_encoder');
        if ($encoder->encode($password, $user->getSalt()) !== $user->getPasswordHash()) {
            $result = ["password_error" => "Parola introdusă este incorectă."];
        } else {
            $result = [
                "username" => $user->getUsername(),
                "webToken" => $user->getWebToken(),
                "role" => $user->getRole()
            ];
        }

        $view = $this->view($result);
        return $this->handleView($view);
    }

    public function validateTokenAction(Request $request)
    {
        $accessLevel = $request->request->get('access_level');
        $token = $request->request->get('_CWT');
        $tokenData = $this->get('security.token_manipulator')->extract($token);
        if ($tokenData === null) {
            $result = $this->view(["allow" => 0, "error" => "Invalid token"]);
        }

        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(["username" => $tokenData["username"]]);
        if ($user === null) {
            $result = $this->view(["allow" => 0, "error" => "User associated was not found"]);
            return $this->handleView($result);
        }

        $seed = $user->getRole() . User::TOKEN_SALT . $user->getPasswordHash();
        $encoder = $this->get('security.string_encoder');
        if ($encoder->encode($seed) !== $tokenData["hash"]) {
            $result = $this->view(["allow" => 0, "error" => "Token hash is invalid"]);
        } else {
            $roleManager = $this->get('security.roles');
            if ($roleManager->hasRole($user, $roleManager->get($accessLevel))) {
                $result = $this->view(["allow" => 1]);
            } else {
                $result = $this->view(["allow" => 2]);
            }
        }

        return $this->handleView($result);
    }

    public function getCurrentUserAction(Request $request)
    {
        $token = $request->request->get("_CWT");
        $tokenData = $this->get('security.token_manipulator')->extract($token);

        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->findOneBy(["username" => $tokenData["username"]]);
        return $this->response([
            "username" => $user->getUsername(),
            "role" => $user->getRole(),
        ]);
    }

    public function registerAction(Request $request)
    {
        $encoder = $this->get('security.string_encoder');
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $group = $request->request->get('group');
        $cnp = $request->request->get('cnp');
        $firstname = trim($firstname);
        $username = preg_replace("/\s+|-+/", "_", strtolower($firstname));
        $username = $username . '.' . preg_replace("/\s+|-+/", "_", strtolower($lastname));

        $salt = $encoder->generateSalt(6);
        $passwordHash = $encoder->encode($request->request->get('password'), $salt);
        $email = $request->request->get('email');

        $em = $this->getDoctrine()->getManager();
        $searchByUser = $em->getRepository('AppBundle:User')->findOneBy(["username" => $username]);
        if ($searchByUser !== null) {
            return $this->response(["username_error" => "Acest utilizator deja există."]);
            /**
             * >TODO
             * Append something to the username in case there are two students with the same name
             */
        }

        $searchByEmail = $em->getRepository('AppBundle:User')->findOneBy(["email" => $email]);
        if ($searchByEmail !== null) {
            return $this->response(["email_error" => "Un utilizator este deja înregistrat cu această adresă de e-mail."]);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setSalt($salt);
        $user->setPasswordHash($passwordHash);
        $user->setEmail($email);
        $user->setRole("Student");
        $user->setWebToken($user->getWebToken());
        $student = new Student();
        $student->setFirstname($firstname);
        $student->setLastname($lastname);
        $student->setCNP($cnp);
        $student->setGroup($group);
        $student->setUser($user);
        $student->setAttendance([]);

        $em->persist($user);
        $em->flush();
        $em->persist($student);
        $em->flush();

        return $this->response([
            "username" => $user->getUsername(),
            "role" => $user->getRole(),
        ]);
    }

    private function response($view) : Response
    {
        return $this->handleView($this->view($view));
    }

    public function setAttendanceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $attendance = $em->getRepository('AppBundle:Attendance')->find($request->request->get('attendance'));
        $code = $attendance->getAttendance();
        $index = $request->request->get('labIndex');

        $mask = pow(2, $index);
        if ($code & $mask) {
            $code = $code & ~$mask;
        } else {
            $code = $code | $mask;
        }

        $attendance->setAttendance($code);
        $em->persist($attendance);
        $em->flush();
        return $this->response($code);
    }

    public function fetchAllAction()
    {
        $result = [];
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();
        foreach ($users as $user) {
            $result[] = [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "role" => $user->getRole(),
            ];
        }

        return $this->response($result);
    }

    public function getAttendanceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $laboratory = $em->getRepository("AppBundle:Laboratory")->find($request->request->get('laboratory'));
        $user = $em->getRepository("AppBundle:User")->findOneBy(["username" => $request->request->get('user')]);
        $student = $em->getRepository("AppBundle:Student")->findOneBy(["user" => $user]);
        if ($student === null) {
            return $this->response(["code" => 404, "message" => "Aparent, contul tău nu are niciun student asociat."]);
        }

        foreach($student->getAttendance() as $labAttendance) {
            if ($labAttendance->getLaboratory() === $laboratory) {
                return $this->response(array(
                    "id" => $labAttendance->getId(),
                    "student" => array(
                        "firstname" => $student->getFirstname(),
                        "lastname" => $student->getLastname(),
                        "CNP" => $student->getCNP(),
                        "group" => $student->getGroup(),
                    ),
                    "laboratory" => array(
                        "name" => $laboratory->getName(),
                        "year" => $laboratory->getYear(),
                        "count" => $laboratory->getCount(),
                        "laborant" => array(
                            "firstname" => $laboratory->getLaborant()->getFirstname(),
                            "lastname" => $laboratory->getLaborant()->getLastname(),
                            "CNP" => $laboratory->getLaborant()->getCNP(),
                        )
                    ),
                    "attendance" => $labAttendance->getAttendance()
                ));
            }
        }

        return $this->response(["error" => "idk what happened"]);
    }

    public function getByUsernameAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Student|Laborant $data */
        if ($user->getRole() !== "Administrator") {
            $data = $em->getRepository("AppBundle:{$user->getRole()}")->findOneBy(["user" => $user]);
            $data->setUser("[Circular]");
            $data->setLaboratories([]);
            $data->setAttendance([]);
            return $this->response([
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "role" => $user->getRole(),
                "personal_info" => $data,
                "other" => [],
            ]);
        } else return $this->response([
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "other" => [],
        ]);
    }


    public function addUserAction(Request $request)
    {
        $userData = $request->request->all();
        $user = $this->get('catalog.users')->createUser($userData);
        return $this->response([
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
        ]);
    }

    public function deleteUserAction(Request $request, string $userId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($userId);
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $author = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($author, $hierarchy->get("administrator"))) {
                    $em->remove($user);
                    $em->flush();
                    return $this->handleView($this->view($user));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }

    public function editUserAction(Request $request, string $userId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($userId);
        try {
            $tokenManipulator = $this->get('security.token_manipulator');
            if ($tokenManipulator->isValid($request->request->get("_CWT"))) {
                $author = $tokenManipulator->getUser($request->request->get("_CWT"));
                $hierarchy = $this->get('security.roles');
                if ($hierarchy->hasRole($author, $hierarchy->get("administrator"))) {
                    $user->setUsername($request->request->get('username'));
                    $user->setEmail($request->request->get('email'));
                    $user->setRole($request->request->get('role'));
                    $em->persist($user);
                    $em->flush();
                    return $this->handleView($this->view($user));
                } else throw new Exception("Se pare ca nu ai dreptul să execuţi această acţiune. Vorbeşte cu un administrator.");
            } else throw new Exception("Se pare că tokenul tău este invalid.");
        } catch (Exception $ex) {
            return $this->handleView($this->view(["error" => $ex->getMessage()]));
        }
    }
}