<?php

namespace DachcomBundle\Test\Support\Test;

use Dachcom\Codeception\Support\Test\BundleTestCase;
use Dachcom\Codeception\Support\Util\SystemHelper;
use DachcomBundle\Test\Support\Util\MembersHelper;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\SsoIdentityManager;
use MembersBundle\Manager\UserManager;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Pimcore\Tests\Support\Util\TestHelper;

abstract class DachcomBundleTestCase extends BundleTestCase
{
    protected function _after(): void
    {
        SystemHelper::cleanUp(['members_restrictions', 'members_group_relations']);
        MembersHelper::reCreateMembersStructure();
    }

    protected function createUser(bool $published = false, array $groups = []): UserInterface
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

    protected function createSsoIdentity(bool $published, string $provider, string $identifier): SsoIdentityInterface
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

    protected function createUserGroup(string $key = 'group-1', array $roles = []): DataObject\MembersGroup
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

    protected function createRestrictedDocument(array $groups = []): Page
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
