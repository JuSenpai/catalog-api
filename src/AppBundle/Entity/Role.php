<?php

namespace AppBundle\Entity;


class Role
{
    private $role;
    private $children = [];
    private $label;

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function includes(Role $role)
    {
        $this->children[] = $role;
        foreach ($role->getChildren() as $childRole) {
            $this->includes($childRole);
        }
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function __toString()
    {
        return $this->role;
    }
}