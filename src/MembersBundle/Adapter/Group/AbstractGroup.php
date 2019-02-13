<?php

namespace MembersBundle\Adapter\Group;

use Pimcore\Model\DataObject\Concrete;

abstract class AbstractGroup extends Concrete implements GroupInterface
{
    use GroupTrait;
}
