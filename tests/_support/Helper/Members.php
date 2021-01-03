<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Dachcom\Codeception\Helper\PimcoreCore;
use Dachcom\Codeception\Util\SystemHelper;
use Dachcom\Codeception\Util\VersionHelper;
use DachcomBundle\Test\Util\MembersHelper;
use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManager;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Security\RestrictionUri;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;
use Symfony\Component\DependencyInjection\Container;

class Members extends Module implements DependsOnModule
{
    /**
     * @var PimcoreBackend
     */
    protected $pimcoreBackend;

    /**
     * @return array|mixed
     */
    public function _depends()
    {
        return [
            'DachcomBundle\Test\Helper\PimcoreBackend' => 'Members needs the PimcoreBackend module to work.'
        ];
    }

    /**
     * @param PimcoreBackend $connection
     */
    public function _inject(PimcoreBackend $connection)
    {
        $this->pimcoreBackend = $connection;
    }

    /**
     * API Function to create area elements for members
     *
     * @param null|Page    $redirectAfterSuccessDocument
     * @param null|Snippet $loginSnippet
     * @param bool         $hideAreaAfterLogin
     *
     * @return array
     */
    public function haveMembersAreaEditables($redirectAfterSuccessDocument = null, $loginSnippet = null, $hideAreaAfterLogin = false)
    {
        if (VersionHelper::pimcoreVersionIsGreaterOrEqualThan('6.8.0')) {
            $blockAreaClass = 'Pimcore\Model\Document\Editable\Areablock';
            $checkboxClass = 'Pimcore\Model\Document\Editable\Checkbox';
            $relationClass = 'Pimcore\Model\Document\Editable\Relation';
        } else {
            $blockAreaClass = 'Pimcore\Model\Document\Tag\Areablock';
            $checkboxClass = 'Pimcore\Model\Document\Tag\Checkbox';
            $relationClass = 'Pimcore\Model\Document\Tag\Relation';
        }

        $blockArea = new $blockAreaClass();
        $blockArea->setName(SystemHelper::AREA_TEST_NAMESPACE);

        $redirectAfterSuccess = null;
        if ($redirectAfterSuccessDocument instanceof Page) {
            $redirectAfterSuccess = new $relationClass();
            $redirectAfterSuccess->setName(sprintf('%s:1.redirectAfterSuccess', SystemHelper::AREA_TEST_NAMESPACE));
            $data = [
                'id'      => $redirectAfterSuccessDocument->getId(),
                'type'    => 'document',
                'subtype' => $redirectAfterSuccessDocument->getType()
            ];
            $redirectAfterSuccess->setDataFromEditmode($data);
        }

        $hideWhenLoggedIn = new $checkboxClass();
        $hideWhenLoggedIn->setName(sprintf('%s:1.hideWhenLoggedIn', SystemHelper::AREA_TEST_NAMESPACE));
        $hideWhenLoggedIn->setDataFromEditmode($hideAreaAfterLogin);

        $showSnippedWhenLoggedIn = null;
        if ($loginSnippet instanceof Snippet) {
            $showSnippedWhenLoggedIn = new $relationClass();
            $showSnippedWhenLoggedIn->setName(sprintf('%s:1.showSnippedWhenLoggedIn', SystemHelper::AREA_TEST_NAMESPACE));

            $data2 = [
                'id'      => $loginSnippet->getId(),
                'type'    => 'document',
                'subtype' => $loginSnippet->getType()
            ];

            $showSnippedWhenLoggedIn->setDataFromEditmode($data2);
        }

        $blockArea->setDataFromEditmode([
            [
                'key'    => '1',
                'type'   => 'members_login',
                'hidden' => false
            ]
        ]);

        $data = [
            sprintf('%s', SystemHelper::AREA_TEST_NAMESPACE)                    => $blockArea,
            sprintf('%s:1.hideWhenLoggedIn', SystemHelper::AREA_TEST_NAMESPACE) => $hideWhenLoggedIn
        ];

        if ($redirectAfterSuccess !== null) {
            $data[sprintf('%s:1.redirectAfterSuccess', SystemHelper::AREA_TEST_NAMESPACE)] = $redirectAfterSuccess;
        }

        if ($showSnippedWhenLoggedIn !== null) {
            $data[sprintf('%s:1.showSnippedWhenLoggedIn', SystemHelper::AREA_TEST_NAMESPACE)] = $showSnippedWhenLoggedIn;
        }

        return $data;
    }

    public function haveAProtectedAssetFolder()
    {
        return Asset::getByPath('/' . RestrictionUri::PROTECTED_ASSET_FOLDER);
    }

    /**
     * Actor Function to create a frontend user group.
     *
     * @param string $name
     *
     * @return DataObject\MembersGroup
     * @throws \Exception
     */
    public function haveAFrontendUserGroup(string $name = 'Group 1')
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
     *
     * @param bool  $confirmed
     * @param array $groups
     *
     * @return mixed
     * @throws ModuleException
     */
    public function haveARegisteredFrontEndUser(bool $confirmed = false, array $groups = [])
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

    /**
     * Actor Function to publish and confirm (triggered by updateUser()) a frontend user.
     *
     * @param UserInterface $user
     *
     * @throws ModuleException
     */
    public function publishAndConfirmAFrontendUser(UserInterface $user)
    {
        $user->setPublished(true);

        $userManager = $this->getContainer()->get(UserManager::class);
        $userManager->updateUser($user);

        $this->assertTrue($user->getPublished());
    }

    /**
     * Actor function to see a logged in frontend user in session bag.
     *
     * @throws ModuleException
     */
    public function seeALoggedInFrontEndUser()
    {
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        $this->assertNotNull($tokenStorage->getToken());
        $this->assertInstanceOf(UserInterface::class, $tokenStorage->getToken()->getUser());
    }

    /**
     * Actor Function to see a not logged in frontend user in session bag.
     *
     * @throws ModuleException
     */
    public function seeANotLoggedInFrontEndUser()
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
     *
     * @param UserInterface $user
     * @param array         $expectedProperties
     */
    public function seePropertiesInFrontendUser(UserInterface $user, array $expectedProperties = [])
    {
        $userProperties = $user->getProperties();
        foreach ($expectedProperties as $property) {
            $this->assertArrayHasKey($property, $userProperties);
        }
    }

    /**
     * Actor Function to get confirmation link from email
     *
     * @param Email $email
     *
     * @return string
     */
    public function haveConfirmationLinkInEmail(Email $email)
    {
        $foundEmails = $this->pimcoreBackend->getEmailsFromDocumentIds([$email->getId()]);
        $serializer = $this->pimcoreBackend->getSerializer();

        $propertyKey = 'confirmationUrl';
        $link = null;
        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            $key = array_search($propertyKey, array_column($params, 'key'));
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
     *
     * @throws \Exception
     */
    public function seeNoFrontendUserInStorage()
    {
        $list = MembersUser::getList(['unpublished' => true]);
        $users = $list->load();

        $this->assertCount(0, $users);
    }

    /**
     * Actor Function to check if the last registered user has an valid token.
     *
     * @throws \Exception
     */
    public function seeAUserWithValidToken()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNotEmpty($user->getConfirmationToken());
    }

    /**
     * Actor Function to check if the last registered user has an invalid token.
     *
     * @throws \Exception
     */
    public function seeAUserWithInvalidatedToken()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNull($user->getConfirmationToken());
    }

    /**
     * Actor Function to check if the last registered user is published.
     *
     * @throws \Exception
     */
    public function seeAPublishedUserAfterRegistration()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertTrue($user->getPublished());
    }

    /**
     * Actor Function to check if the last registered user is unpublished.
     *
     * @throws \Exception
     */
    public function seeAUnpublishedUserAfterRegistration()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertFalse($user->getPublished());
    }

    /**
     * Actor function to get the last registered frontend user.
     * Only one user in storage is allowed here.
     *
     * @return UserInterface
     * @throws \Exception
     */
    public function grabOneUserAfterRegistration()
    {
        $list = MembersUser::getList(['unpublished' => true]);
        $users = $list->getObjects();

        $this->assertCount(1, $users);
        $this->assertInstanceOf(UserInterface::class, $users[0]);

        return $users[0];
    }

    /**
     * Actor function to add restriction to object
     *
     * @param AbstractObject $object
     * @param array          $groups
     * @param bool           $inherit
     * @param bool           $inherited
     */
    public function addRestrictionToObject(AbstractObject $object, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($object, 'object', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to add restriction to asset
     *
     * @param Asset $asset
     * @param array $groups
     * @param bool  $inherit
     * @param bool  $inherited
     */
    public function addRestrictionToAsset(Asset $asset, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($asset, 'asset', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor function to add restriction to document
     *
     * @param Document $document
     * @param array    $groups
     * @param bool     $inherit
     * @param bool     $inherited
     */
    public function addRestrictionToDocument(Document $document, $groups = [], $inherit = false, $inherited = false)
    {
        $restriction = $this->createElementRestriction($document, 'page', $groups, $inherit, $inherited);
        $this->assertInstanceOf(Restriction::class, $restriction);
    }

    /**
     * Actor Function to generate asset download link with containing a single asset file.
     *
     * @param Asset $asset
     *
     * @return string
     * @throws ModuleException
     * @throws \Exception
     */
    public function haveASingleAssetDownloadLink(Asset $asset)
    {
        $downloadLink = $this
            ->getContainer()->get(RestrictionUri::class)
            ->generateAssetUrl($asset);

        $this->assertInternalType('string', $downloadLink);

        return $downloadLink;
    }

    /**
     * Actor Function to generate asset download link with containing multiple assets.
     *
     * @param array $assets
     *
     * @return string
     * @throws ModuleException
     * @throws \Exception
     */
    public function haveAMultipleAssetDownloadLink(array $assets)
    {
        $downloadLink = $this
            ->getContainer()->get(RestrictionUri::class)
            ->generateAssetPackageUrl($assets);

        $this->assertInternalType('string', $downloadLink);

        return $downloadLink;
    }

    /**
     * @param        $element
     * @param string $type
     * @param array  $groups
     * @param bool   $inherit
     * @param bool   $inherited
     *
     * @return Restriction
     */
    protected function createElementRestriction(
        $element,
        string $type = 'page',
        array $groups = [],
        bool $inherit = false,
        bool $inherited = false
    ) {
        $restriction = new Restriction();
        $restriction->setTargetId($element->getId());
        $restriction->setCtype($type);
        $restriction->setInherit($inherit);
        $restriction->setIsInherited($inherited);
        $restriction->setRelatedGroups($groups);
        $restriction->save();

        return $restriction;
    }

    /**
     * @return Container
     * @throws ModuleException
     */
    protected function getContainer()
    {
        return $this->getModule('\\' . PimcoreCore::class)->getContainer();
    }
}
