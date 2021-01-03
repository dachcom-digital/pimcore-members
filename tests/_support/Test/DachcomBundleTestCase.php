<?php

namespace DachcomBundle\Test\Test;

use Codeception\Exception\ModuleException;
use Dachcom\Codeception\Util\SystemHelper;
use DachcomBundle\Test\Util\MembersHelper;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\SsoIdentityManager;
use MembersBundle\Manager\UserManager;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Pimcore\Tests\Util\TestHelper;

abstract class DachcomBundleTestCase extends \Dachcom\Codeception\Test\DachcomBundleTestCase
{
    protected function _after()
    {
        SystemHelper::cleanUp(['members_restrictions', 'members_group_relations']);
        MembersHelper::reCreateMembersStructure();
    }

    /**
     * @param bool  $published
     * @param array $groups
     *
     * @return mixed
     * @throws ModuleException
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
     * @param bool   $published
     * @param string $provider
     * @param string $identifier
     *
     * @return SsoIdentityInterface
     * @throws ModuleException
     */
    protected function createSsoIdentity($published, $provider, $identifier)
    {
        $user = $this->createUser($published);

        $userManager = $this->getContainer()->get(UserManager::class);
        $ssoIdentityManager = $this->getContainer()->get(SsoIdentityManager::class);

        $ssoIdentity = $ssoIdentityManager->createSsoIdentity($user, $provider, $identifier, '');
        $ssoIdentityManager->saveIdentity($ssoIdentity);
        $ssoIdentityManager->addSsoIdentity($user, $ssoIdentity);

        $userManager->updateUser($user);

        return $ssoIdentity;
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
     * @return Page
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
}
