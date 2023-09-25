<?php

namespace MembersBundle\Adapter\User;

use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

abstract class AbstractSsoAwareUser extends Concrete implements UserInterface, SsoAwareUserInterface, PasswordAuthenticatedUserInterface
{
    use UserTrait;
}
