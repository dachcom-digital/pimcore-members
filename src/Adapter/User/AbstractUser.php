<?php

namespace MembersBundle\Adapter\User;

use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

abstract class AbstractUser extends Concrete implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UserTrait;
}
