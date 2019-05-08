<?php

namespace DachcomBundle\Test\Test;

use DachcomBundle\Test\Helper\PimcoreCore;
use DachcomBundle\Test\Util\FileGeneratorHelper;
use DachcomBundle\Test\Util\MembersHelper;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManager;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model\DataObject;
use Pimcore\Tests\Test\TestCase;
use Pimcore\Tests\Util\TestHelper;

abstract class DachcomBundleTestCase extends TestCase
{
    protected function _after()
    {
        MembersHelper::cleanUp();
        MembersHelper::reCreateMembersStructure();
        FileGeneratorHelper::cleanUp();

        parent::_after();
    }

    /**
     * @param bool  $published
     * @param array $groups
     *
     * @return mixed
     * @throws \Codeception\Exception\ModuleException
     */
    protected function createUser($published = false, $groups = [])
    {
        $userManager = $this->getContainer()->get(UserManager::class);
        $configuration = $this->getContainer()->get(Configuration::class);

        $membersStoreObject = DataObject::getByPath($configuration->getConfig('storage_path'));

        $userObject = $userManager->createUser();
        $userObject->setParent($membersStoreObject);
        $userObject->setEmail(MembersHelper::DEFAULT_FEU_EMAIL);
        $userObject->setUserName(MembersHelper::DEFAULT_FEU_USERNAME);
        $userObject->setPlainPassword(MembersHelper::DEFAULT_FEU_PASSWORD);
        $userObject->setPublished($published);

        $user = $userManager->updateUser($userObject);

        if (count($groups) > 0) {
            $user->setGroups($groups);
            $userManager->updateUser($user);
        }

        return $user;
    }

    /**
     * @param string $key
     * @param array  $roles
     *
     * @return DataObject\MembersGroup
     * @throws \Exception
     */
    protected function createUserGroup($key = 'group-1', $roles = [])
    {
        $group = new DataObject\MembersGroup();
        $group->setKey($key);
        $group->setName(MembersHelper::DEFAULT_FEG_NAME);
        $group->setPublished(true);
        $group->setParent(DataObject::getByPath('/'));
        $group->setRoles($roles);
        $group->save();

        return $group;
    }

    /**
     * @param array $groups
     *
     * @return \Pimcore\Model\Document\Page
     */
    protected function createRestrictedDocument($groups = [])
    {
        $document = TestHelper::createEmptyDocumentPage('restricted-document');

        if (count($groups) > 0) {
            $restriction = new Restriction();
            $restriction->setTargetId($document->getId());
            $restriction->setCtype('page');

            $restriction->setInherit(false);
            $restriction->setIsInherited(false);
            $restriction->setRelatedGroups($groups);
            $restriction->save();
        }

        return $document;

    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getContainer()
    {
        return $this->getPimcoreBundle()->getContainer();
    }

    /**
     * @return PimcoreCore
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getPimcoreBundle()
    {
        return $this->getModule('\\' . PimcoreCore::class);
    }
}
