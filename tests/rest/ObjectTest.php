<?php

namespace DachcomBundle\Test\rest;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\MembersGroup;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Tests\Util\TestHelper;

class ObjectTest extends AbstractRestTestCase
{
    public function testUserCreate()
    {
        $unsavedObject = new MembersUser();
        $unsavedObject->setParentId(1);
        $unsavedObject->setUserOwner(1);
        $unsavedObject->setUserModification(1);
        $unsavedObject->setPublished(1);
        $unsavedObject->setCreationDate(time());
        $unsavedObject->setKey(uniqid() . rand(10, 99));

        $time = time();

        $result = $this->restClient->createObjectConcrete($unsavedObject);

        $this->assertTrue($result->success, 'request not successful . ' . $result->msg);
        $this->assertEquals(2, TestHelper::getObjectCount());

        $id = $result->id;
        $this->assertTrue($id > 1, 'id must be greater than 1');

        $objectDirect = AbstractObject::getById($id);
        $creationDate = $objectDirect->getCreationDate();

        $this->assertTrue($creationDate >= $time, 'wrong creation date');

        // as the object key is unique there must be exactly one object with that key
        $list = $this->restClient->getObjectList('{ "o_key" : "' . $unsavedObject->getKey() . '"}');
        $this->assertEquals(1, count($list));

        $fetchedObject = $this->restClient->getObjectById($id);
        $this->assertInstanceOf(UserInterface::class, $fetchedObject);
    }

    public function testUserGroupCreate()
    {
        $unsavedObject = new MembersGroup();
        $unsavedObject->setParentId(1);
        $unsavedObject->setUserOwner(1);
        $unsavedObject->setUserModification(1);
        $unsavedObject->setPublished(1);
        $unsavedObject->setCreationDate(time());
        $unsavedObject->setKey(uniqid() . rand(10, 99));

        $time = time();

        $result = $this->restClient->createObjectConcrete($unsavedObject);

        $this->assertTrue($result->success, 'request not successful . ' . $result->msg);
        $this->assertEquals(2, TestHelper::getObjectCount());

        $id = $result->id;
        $this->assertTrue($id > 1, 'id must be greater than 1');

        $objectDirect = AbstractObject::getById($id);
        $creationDate = $objectDirect->getCreationDate();

        $this->assertTrue($creationDate >= $time, 'wrong creation date');

        // as the object key is unique there must be exactly one object with that key
        $list = $this->restClient->getObjectList('{ "o_key" : "' . $unsavedObject->getKey() . '"}');
        $this->assertEquals(1, count($list));

        $fetchedObject = $this->restClient->getObjectById($id);
        $this->assertInstanceOf(GroupInterface::class, $fetchedObject);
    }
}
