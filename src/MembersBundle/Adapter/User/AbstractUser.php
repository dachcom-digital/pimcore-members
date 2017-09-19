<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Password;
use Pimcore\Model\DataObject\Concrete;

abstract class AbstractUser extends Concrete implements UserInterface
{
    use UserTrait;
}