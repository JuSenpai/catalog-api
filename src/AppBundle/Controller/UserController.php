<?php

namespace AppBundle\Controller;

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
        $username = $request->request->get('username');
        $salt = $encoder->generateSalt(6);
        $passwordHash = $encoder->encode($request->request->get('password'), $salt);
        $email = $request->request->get('email');

        $em = $this->getDoctrine()->getManager();
        $searchByUser = $em->getRepository('AppBundle:User')->findOneBy(["username" => $username]);
        if ($searchByUser !== null) {
            return $this->response(["username_error" => "Acest utilizator deja există."]);
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

        $em->persist($user);
        $em->flush();

        return $this->response([
            "username" => $user->getUsername(),
            "role" => $user->getRole(),
        ]);
    }

    private function response(array $view) : Response
    {
        return $this->handleView($this->view($view));
    }
}