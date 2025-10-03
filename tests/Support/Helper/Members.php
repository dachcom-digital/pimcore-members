<?php

namespace DachcomBundle\Test\Support\Helper;

use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Dachcom\Codeception\Support\Helper\PimcoreCore;
use DachcomBundle\Test\Support\Util\MembersHelper;
use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\UserManager;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Security\RestrictionUri;
use MembersBundle\Service\RestrictionService;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\DependencyInjection\Container;

class Members extends Module implements DependsOnModule
{
    protected PimcoreBackend $pimcoreBackend;

    public function _depends(): array
    {
        return [
            PimcoreBackend::class => 'Members needs the PimcoreBackend module to work.'
        ];
    }

    public function _inject(PimcoreBackend $connection): void
    {
        $this->pimcoreBackend = $connection;
    }

    public function haveAProtectedAssetFolder(): Asset
    {
        return Asset::getByPath('/' . RestrictionManager::PROTECTED_ASSET_FOLDER);
    }

    /**
     * Actor Function to create a frontend user group.
     */
    public function haveAFrontendUserGroup(string $name = 'Group 1'): DataObject\MembersGroup
    {
        $group = new DataObject\MembersGroup();
        $group->setKey(File::getValidFilename($name));
        $group->setName($name);
        $group->setPublished(true);
        $group->setParent(DataObject::getByPath('/'));
        $group->save();

        $this->assertInstanceOf(GroupInterface::class, $group);

        return $group;
    }

    /**
     * Actor Function to create a fully registered frontend user. Confirmation is optionally.
     */
    public function haveARegisteredFrontEndUser(bool $confirmed = false, array $groups = [], array $additionalParameter = []): UserInterface
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $membersStoreObject = DataObject::getByPath($configuration->getConfig('storage_path'));

        $userManager = $this->getContainer()->get(UserManager::class);
        $userObject = $userManager->createUser();

        $userObject->setParent($membersStoreObject);
        $userObject->setEmail(MembersHelper::DEFAULT_FEU_EMAIL);
        $userObject->setUserName(MembersHelper::DEFAULT_FEU_USERNAME);
        $userObject->setPlainPassword(MembersHelper::DEFAULT_FEU_PASSWORD);
        $userObject->setPublished(false);
        $userObject->setConfirmationToken(MembersHelper::DEFAULT_CONFIRMATION_TOKEN);

        if (count($additionalParameter) > 0) {
            foreach ($additionalParameter as $additionalParam => $additionalParamValue) {
                $userObject->setObjectVar($additionalParam, $additionalParamValue);
            }
        }

        $user = $userManager->updateUser($userObject);

        if (count($groups) > 0) {
            $user->setGroups($groups);
            $userManager->updateUser($user);
        }

        if ($confirmed === true) {
            $this->publishAndConfirmAFrontendUser($user);
        }

        $this->assertInstanceOf(UserInterface::class, $user);

        return $user;
    }

    public function haveAConfirmedUnpublishedFrontEndUser(array $groups = [], array $additionalParameter = []): UserInterface
    {
        $user = $this->haveARegisteredFrontEndUser(true, $groups, $additionalParameter);

        $user->setPublished(false);

        $userManager = $this->getContainer()->get(UserManager::class);
        $userManager->updateUser($user);

        $this->assertInstanceOf(UserInterface::class, $user);

        return $user;
    }

    /**
     * Actor Function to publish and confirm (triggered by updateUser()) a frontend user.
     */
    public function publishAndConfirmAFrontendUser(UserInterface $user): void
    {
        $user->setPublished(true);
        $user->setConfirmationToken(null);

        $userManager = $this->getContainer()->get(UserManager::class);
        $userManager->updateUser($user);

        $this->assertTrue($user->getPublished());
        $this->assertNull($user->getConfirmationToken());
    }

    /**
     * Actor Function to publish (triggered by updateUser()) a frontend user.
     */
    public function publishAFrontendUser(UserInterface $user): void
    {
        $user->setPublished(true);

        $userManager = $this->getContainer()->get(UserManager::class);
        $userManager->updateUser($user);

        $this->assertTrue($user->getPublished());
    }

    /**
     * Actor function to see a logged in frontend user in session bag.
     */
    public function seeALoggedInFrontEndUser(): void
    {
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        $this->assertNotNull($tokenStorage->getToken());
        $this->assertInstanceOf(UserInterface::class, $tokenStorage->getToken()->getUser());
    }

    /**
     * Actor Function to see a not logged in frontend user in session bag.
     */
    public function seeANotLoggedInFrontEndUser(): void
    {
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        // null is ok in this case!
        if (is_null($tokenStorage->getToken())) {
            return;
        }

        $this->assertSame('anon.', $tokenStorage->getToken()->getUser());
    }

    /**
     * Actor Function to see properties in members user object
     */
    public function seePropertiesInFrontendUser(UserInterface $user, array $expectedProperties = []): void
    {
        $userProperties = $user->getProperties();
        foreach ($expectedProperties as $property) {
            $this->assertArrayHasKey($property, $userProperties);
        }
    }

    /**
     * Actor Function to get confirmation link from email
     */
    public function haveConfirmationLinkInEmail(Email $email): string
    {
        $foundEmails = $this->pimcoreBackend->getEmailsFromDocumentIds([$email->getId()]);

        $propertyKey = 'confirmationUrl';
        $link = null;
        foreach ($foundEmails as $foundEmail) {
            $params = $foundEmail->getParams();
            $key = array_search($propertyKey, array_column($params, 'key'), true);
            if ($key === false) {
                $this->fail(sprintf('Failed asserting that mail params array has the key "%s".', $propertyKey));
            } else {
                $data = $params[$key];
                $link = $data['data']['value'];
            }
            break;
        }

        $this->assertNotEmpty($link);

        return $link;
    }

    /**
     * Actor Function to check if no users are available in storage.
     */
    public function seeNoFrontendUserInStorage(): void
    {
        $list = MembersUser::getList(['unpublished' => true]);
        $users = $list->load();

        $this->assertCount(0, $users);
    }

    /**
     * Actor Function to check if the last registered user has an valid token.
     */
    public function seeAUserWithValidToken(): void
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNotEmpty($user->getConfirmationToken());
    }

    /**
     * Actor Function to check if the last registered user has an invalid token.
     */
    public function seeAUserWithInvalidatedToken(): void
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNull($user->getConfirmationToken());
    }

    /**
     * Actor Function to check if the last registered user is published.
     */
    public function seeAPublishedUserAfterRegistration(): void
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertTrue($user->getPublished());
    }

    /**
     * Actor Function to check if the last registered user is unpublished.
     */
    public function seeAUnpublishedUserAfterRegistration(): void
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertFalse($user->getPublished());
    }

    /**
     * Actor function to get the last registered frontend user.
     * Only one user in storage is allowed here.
     */
    public function grabOneUserAfterRegistration(): UserInterface
    {
        $list = MembersUser::getList(['unpublished' => true]);
        $users = $list->getObjects();

        $this->assertCount(1, $users);
        $this->assertInstanceOf(UserInterface::class, $users[0]);

        return $users[0];
    }

    /**
     * Actor function to add restriction to object
     */
    public function addRestrictionToObject(AbstractObject $object, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($object, 'object', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to change restriction to object
     */
    public function changeRestrictionToObject(AbstractObject $object, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($object, 'object', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to add restriction to asset
     */
    public function addRestrictionToAsset(Asset $asset, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($asset, 'asset', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to change restriction to asset
     */
    public function changeRestrictionToAsset(Asset $asset, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($asset, 'asset', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to add restriction to document
     */
    public function addRestrictionToDocument(Document $document, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($document, 'page', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to change restriction to document
     */
    public function changeRestrictionToDocument(Document $document, array $groups = [], bool $inherit = false, bool $inherited = false): void
    {
        $restriction = $this->createElementRestriction($document, 'page', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to see restriction on element
     */
    public function seeRestrictionOnEntity(ElementInterface $element): void
    {
        $restriction = null;

        try {
            $type = $this->getEntityRestrictionType($element);
            $restriction = Restriction::getByTargetId($element->getId(), $type);
        } catch (\Throwable $e) {
            // fail silently
        }

        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to see no restriction on element
     */
    public function seeNoRestrictionOnEntity(ElementInterface $element): void
    {
        $restriction = null;

        try {
            $type = $this->getEntityRestrictionType($element);
            $restriction = Restriction::getByTargetId($element->getId(), $type);
        } catch (\Throwable $e) {
            // fail silently
        }

        $this->assertEquals(null, $restriction);
    }

    /**
     * Actor function to see restriction with groups on element
     */
    public function seeRestrictionWithGroupsOnEntity(ElementInterface $element, $groups = []): void
    {
        $restriction = null;

        try {
            $type = $this->getEntityRestrictionType($element);
            $restriction = Restriction::getByTargetId($element->getId(), $type);
        } catch (\Throwable $e) {
            // fail silently
        }

        $groupIds = array_map(static function (GroupInterface $group) {
            return $group->getId();
        }, $groups);

        $restrictionGroupIds = $restriction->getRelatedGroups();

        $this->assertEquals(sort($groupIds), sort($restrictionGroupIds));
    }

    /**
     * Actor function to see inherited restriction on element
     */
    public function seeInheritedRestrictionOnEntity(ElementInterface $element): void
    {
        $restriction = null;

        try {
            $type = $this->getEntityRestrictionType($element);
            $restriction = Restriction::getByTargetId($element->getId(), $type);
        } catch (\Throwable $e) {
            // fail silently
        }

        $this->assertTrue($restriction->getIsInherited());
    }

    /**
     * Actor function to see no inherited restriction on element
     */
    public function seeNoInheritedRestrictionOnEntity(ElementInterface $element): void
    {
        $restriction = null;

        try {
            $type = $this->getEntityRestrictionType($element);
            $restriction = Restriction::getByTargetId($element->getId(), $type);
        } catch (\Throwable $e) {
            // fail silently
        }

        $this->assertFalse($restriction->getIsInherited());
    }

    /**
     * Actor Function to generate asset download link with containing a single asset file.
     */
    public function haveASingleAssetDownloadLink(Asset $asset): string
    {
        $downloadLink = $this->getContainer()->get(RestrictionUri::class)->generateAssetUrl($asset);

        $this->assertIsString($downloadLink);

        return $downloadLink;
    }

    /**
     * Actor Function to generate asset download link with containing multiple assets.
     */
    public function haveAMultipleAssetDownloadLink(array $assets): string
    {
        $downloadLink = $this->getContainer()->get(RestrictionUri::class)->generateAssetPackageUrl($assets);

        $this->assertIsString($downloadLink);

        return $downloadLink;
    }

    protected function createElementRestriction(
        $element,
        string $type = 'page',
        array $groups = [],
        bool $inherit = false,
        bool $inherited = false
    ): Restriction {
        $restrictionService = $this->getContainer()->get(RestrictionService::class);

        return $restrictionService->createRestriction($element, $type, $inherit, $inherited, $groups);
    }

    protected function getContainer(): Container
    {
        return $this->getModule('\\' . PimcoreCore::class)->_getContainer();
    }

    protected function getEntityRestrictionType(ElementInterface $element): string
    {
        if ($element instanceof Document) {
            return 'page';
        }

        if ($element instanceof DataObject) {
            return 'object';
        }

        if ($element instanceof Asset) {
            return 'asset';
        }

        return '';
    }
}
