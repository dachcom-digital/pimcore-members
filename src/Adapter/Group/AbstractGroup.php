<?php

namespace MembersBundle\Adapter\Group;

use Pimcore\Model\Object\Concrete;

abstract class AbstractGroup extends Concrete implements GroupInterface
{
    public function getRoles()
    {
        return [];
    }
}