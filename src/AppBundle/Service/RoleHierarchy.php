<?php

namespace AppBundle\Service;


use AppBundle\Entity\Role;
use AppBundle\Entity\User;

class RoleHierarchy
{
    private $administrator;
    private $laborant;
    private $student;
    private $user_auth;

    public function __construct()
    {
        $this->user_auth = $this->role()
            ->setRole("user_auth")
            ->setLabel("User");
        $this->student = (new Role())
            ->setRole("user_stud")
            ->setLabel("Student")
            ->includes($this->user_auth);
        $this->laborant = (new Role())
            ->setRole("user_lab")
            ->setLabel("Laborant")
            ->includes($this->user_auth);
        $this->administrator = (new Role())
            ->setRole("sys_admin")
            ->setLabel("Administrator")
            ->includes($this->laborant)
            ->includes($this->student);
    }

    public function role()
    {
        return new Role();
    }

    public function get($role)
    {
        $role = strtolower($role);
        return $this->$role ?? null;
    }

    public function includes(Role $parent, Role $child) : bool
    {
        if ($parent === $child) {
            return true;
        } else if ($parent->getChildren() === []) {
            return false;
        }

        foreach ($parent->getChildren() as $knownChild) {
            if ($this->includes($knownChild, $child)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(User $user, Role $role) : bool
    {
        return $this->includes($this->get($user->getRole()), $role);
    }
}