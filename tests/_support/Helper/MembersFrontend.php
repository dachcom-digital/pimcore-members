<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManager;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\Document\Email;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MembersFrontend extends Module
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

    public function haveARegisteredFrontEndUser($published = false)
    {
        $userManager = $this->getContainer()->get(UserManager::class);
        $configuration = $this->getContainer()->get(Configuration::class);

        $membersStoreObject = DataObject::getByPath($configuration->getConfig('storage_path'));

        $userObject = $userManager->createUser();
        $userObject->setParent($membersStoreObject);
        $userObject->setEmail('test@universe.org');
        $userObject->setUserName('chuck');
        $userObject->setPlainPassword('test');
        $userObject->setPublished(false);

        $user = $userManager->updateUser($userObject);

        if ($published === true) {
            $user->setConfirmationToken(null);
            $user->setPublished(true);
            $userManager->updateUser($user);
        }

        return $user;
    }

    public function haveALoggedInFrontEndUser()
    {
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        $this->assertNotNull($tokenStorage->getToken());
        $this->assertInstanceOf(UserInterface::class, $tokenStorage->getToken()->getUser());
    }

    public function haveANotLoggedInFrontEndUser()
    {
        $tokenStorage = $this->getContainer()->get('security.token_storage');

        $this->assertNotNull($tokenStorage->getToken());
        $this->assertSame('anon.', $tokenStorage->getToken()->getUser());
    }

    /**
     * Actor Function to get confirmation link from email
     *
     * @param Email $email
     *
     * @return string|null
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

    public function seeAUserWithValidToken()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNotEmpty($user->getConfirmationToken());
    }

    public function seeAUserWithInvalidatedToken()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertNull($user->getConfirmationToken());
    }

    public function seeAPublishedUserAfterRegistration()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertTrue($user->getPublished());
    }

    public function seeAUnpublishedUserAfterRegistration()
    {
        $user = $this->grabOneUserAfterRegistration();
        $this->assertFalse($user->getPublished());
    }

    /**
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
