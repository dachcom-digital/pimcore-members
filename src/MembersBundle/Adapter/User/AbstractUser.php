<?php

namespace MembersBundle\Adapter\User;

use Pimcore\Model\DataObject\Concrete;

abstract class AbstractUser extends Concrete implements UserInterface
{
    use UserTrait;
}