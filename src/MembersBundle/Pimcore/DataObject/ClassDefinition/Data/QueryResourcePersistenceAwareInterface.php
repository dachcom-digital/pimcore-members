<?php

namespace MembersBundle\Pimcore\DataObject\ClassDefinition\Data;

if (interface_exists(\Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface::class)) {
    interface QueryResourcePersistenceAwareInterface extends \Pimcore\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface
    {
    }
} else {
    interface QueryResourcePersistenceAwareInterface
    {
    }
}