<?php

namespace MembersBundle\Security\OAuth\SsoIdentity;

use Doctrine\DBAL\Connection;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\SsoAwareCustomerInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\ClassManagerInterface;
use MembersBundle\Manager\UserManagerInterface;
use Pimcore\File;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\SsoIdentity;

class DefaultSsoIdentityService implements SsoIdentityServiceInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var ClassManagerInterface
     */
    protected $classManager;

    /**
     * @param Connection            $connection
     * @param UserManagerInterface  $userManager
     * @param ClassManagerInterface $classManager
     */
    public function __construct(
        Connection $connection,
        UserManagerInterface $userManager,
        ClassManagerInterface $classManager
    ) {
        $this->connection = $connection;
        $this->userManager = $userManager;
        $this->classManager = $classManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerBySsoIdentity(string $provider, $identifier)
    {
        $ssoIdentity = $this->findSsoIdentity($provider, $identifier);

        if ($ssoIdentity) {
            return $this->findCustomerBySsoIdentity($ssoIdentity);
        }
    }

    /**
     * @param string $provider
     * @param string $identifier
     *
     * @return SsoIdentity
     * @throws \Exception
     */
    protected function findSsoIdentity(string $provider, $identifier)
    {
        $list = new SsoIdentity\Listing();
        $list->addConditionParam('provider = ?', $provider);
        $list->addConditionParam('identifier = ?', $identifier);

        if ($list->count() === 1) {
            return $list->current();
        }

        if ($list->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one identity for %s:%s', $provider, $identifier)
            );
        }
    }

    /**
     * @param SsoIdentityInterface $ssoIdentity
     *
     * @return UserInterface|null
     */
    protected function findCustomerBySsoIdentity(SsoIdentityInterface $ssoIdentity)
    {
        /** @var Concrete $userClass */
        $userClass = $this->classManager->getUserClass();

        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select('src_id')
            ->from('object_relations_' . $userClass::classId())
            ->where('fieldname = :ssoIdentitiesName')
            ->where('dest_id = :ssoIdentitiesId');

        $qb->setParameters([
            'ssoIdentitiesName' => 'ssoIdentities',
            'ssoIdentitiesId'   => $ssoIdentity->getId()
        ]);

        $stmt = $qb->execute();
        $result = $stmt->fetchAll();

        if (count($result) === 1) {
            return $this->userManager->findUserById((int) $result[0]['src_id']);
        }
    }

    /**
     * @param UserInterface|SsoAwareCustomerInterface $customer
     *
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(UserInterface $customer)
    {
        $this->checkCustomer($customer);

        if (empty($customer->getSsoIdentities())) {
            return [];
        }

        return $customer->getSsoIdentities();
    }

    /**
     * @param UserInterface $customer
     * @param string        $provider
     * @param string        $identifier
     *
     * @return SsoIdentityInterface|null
     */
    public function getSsoIdentity(UserInterface $customer, $provider, $identifier)
    {
        foreach ($this->getSsoIdentities($customer) as $ssoIdentity) {
            if ($ssoIdentity->getProvider() === $provider && $ssoIdentity->getIdentifier() === $identifier) {
                return $ssoIdentity;
            }
        }
    }

    /**
     * @param UserInterface|SsoAwareCustomerInterface $customer
     * @param SsoIdentityInterface                    $ssoIdentity
     */
    public function addSsoIdentity(UserInterface $customer, SsoIdentityInterface $ssoIdentity)
    {
        $this->checkCustomer($customer);

        $ssoIdentities = $this->getSsoIdentities($customer);
        $ssoIdentities[] = $ssoIdentity;

        $customer->setSsoIdentities(array_unique($ssoIdentities));
    }

    /**
     * @param UserInterface|Concrete $customer
     * @param string                 $provider
     * @param string                 $identifier
     * @param mixed                  $profileData
     *
     * @return SsoIdentityInterface
     */
    public function createSsoIdentity(UserInterface $customer, $provider, $identifier, $profileData)
    {
        $key = File::getValidFilename(sprintf('%s-%s', $provider, $identifier));
        $path = sprintf('%s/%s', $customer->getRealFullPath(), $key);

        $ssoIdentity = SsoIdentity::getByPath($path);
        if (!$ssoIdentity) {
            $ssoIdentity = new SsoIdentity();
        }

        $ssoIdentity->setPublished(true);
        $ssoIdentity->setKey($key);
        $ssoIdentity->setParent($customer);
        $ssoIdentity->setProvider($provider);
        $ssoIdentity->setIdentifier($identifier);
        $ssoIdentity->setProfileData($profileData);

        return $ssoIdentity;
    }

    /**
     * @param UserInterface $customer
     */
    protected function checkCustomer(UserInterface $customer)
    {
        if (!$customer instanceof SsoAwareCustomerInterface) {
            throw new \RuntimeException('Customer needs to implement SsoAwareCustomerInterface');
        }
    }
}
