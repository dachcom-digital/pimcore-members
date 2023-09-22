<?php

namespace MembersBundle\Adapter\User;

use Pimcore\Model\DataObject\Concrete;

abstract class AbstractSsoAwareUser extends Concrete implements UserInterface, SsoAwareUserInterface
{
    use UserTrait;
}
