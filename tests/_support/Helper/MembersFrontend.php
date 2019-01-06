<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use DachcomBundle\Test\Util\MembersHelper;
use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManager;
use Pimcore\File;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\Document\Email;
use Symfony\Component\DependencyInjection\Container;

class MembersFrontend extends Module implements DependsOnModule
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
        return ['DachcomBundle\Test\Helper\PimcoreBackend' => 'MembersFrontend needs the PimcoreBackend module to work.'];
    }

    /**
     * @param PimcoreBackend $connection
     */
    public function _inject(PimcoreBackend $connection)
    {
        $this->pimcoreBackend = $connection;
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
     * @throws \Codeception\Exception\ModuleException
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
     * @throws \Codeception\Exception\ModuleException
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
     * @throws \Codeception\Exception\ModuleException
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
     * @throws \Codeception\Exception\ModuleException
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
        $users = $list->load();

        $this->assertCount(1, $users);
        $this->assertInstanceOf(UserInterface::class, $users[0]);

        return $users[0];
    }

    /**
     * @return Container
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getContainer()
    {
        return $this->getModule('\\' . PimcoreCore::class)->getContainer();
    }
}
