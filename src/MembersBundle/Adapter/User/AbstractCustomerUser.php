<?php

namespace MembersBundle\Adapter\User;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\Traits\CustomerTrait;
use Pimcore\Model\DataObject\Concrete;

abstract class AbstractCustomerUser extends Concrete implements UserInterface, CustomerInterface
{
    use UserTrait;
    use CustomerTrait;
}