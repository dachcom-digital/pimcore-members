<?php

namespace MembersBundle\Manager;

use Pimcore\File;
use Pimcore\Model\DataObject;
use Doctrine\DBAL\Connection;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\SsoAwareUserInterface;
use MembersBundle\Adapter\User\UserInterface;

class SsoIdentityManager implements SsoIdentityManagerInterface
{
    protected Connection $connection;
    protected UserManagerInterface $userManager;
    protected ClassManagerInterface $classManager;

    public function __construct(
        Connection $connection,
        UserManagerInterface $userManager,
        ClassManagerInterface $classManager
    ) {
        $this->connection = $connection;
        $this->userManager = $userManager;
        $this->classManager = $classManager;
    }

    public function getClass(): string
    {
        return $this->classManager->getSsoIdentityClass();
    }

    public function getUserBySsoIdentity(string $provider, $identifier): ?UserInterface
    {
        $ssoIdentity = $this->findSsoIdentity($provider, $identifier);

        if ($ssoIdentity instanceof SsoIdentityInterface) {
            return $this->findUserBySsoIdentity($ssoIdentity);
        }

        return null;
    }

    public function getSsoIdentities(UserInterface $user): array
    {
        $this->assertSsoAwareUser($user);

        if (!$user instanceof SsoAwareUserInterface) {
            return [];
        }

        if (empty($user->getSsoIdentities())) {
            return [];
        }

        return $user->getSsoIdentities() ?? [];
    }

    public function getSsoIdentity(UserInterface $user, string $provider, string $identifier): ?SsoIdentityInterface
    {
        foreach ($this->getSsoIdentities($user) as $ssoIdentity) {
            if ($ssoIdentity->getProvider() === $provider && $ssoIdentity->getIdentifier() === $identifier) {
                return $ssoIdentity;
            }
        }

        return null;
    }

    public function addSsoIdentity(UserInterface $user, SsoIdentityInterface $ssoIdentity): void
    {
        $this->assertSsoAwareUser($user);

        if (!$user instanceof SsoAwareUserInterface) {
            return;
        }

        $ssoIdentities = $this->getSsoIdentities($user);
        $ssoIdentities[] = $ssoIdentity;

        $user->setSsoIdentities(array_unique($ssoIdentities));
    }

    public function createSsoIdentity(UserInterface $user, string $provider, string $identifier, string $profileData): SsoIdentityInterface
    {
        if (!$user instanceof DataObject\Concrete) {
            throw new \RuntimeException('User needs to be an instance of Concrete');
        }

        $key = File::getValidFilename(sprintf('%s-%s', $provider, $identifier));
        $path = sprintf('%s/%s', $user->getRealFullPath(), $key);

        /** @var SsoIdentityInterface $ssoIdentity */
        $ssoIdentity = DataObject::getByPath($path);

        if (!$ssoIdentity instanceof SsoIdentityInterface) {
            $ssoIdentityClass = $this->classManager->getSsoIdentityClass();
            $ssoIdentity = new $ssoIdentityClass();
        }

        if (!$ssoIdentity instanceof DataObject\Concrete) {
            throw new \RuntimeException('Sso Identity needs to be an instance of Concrete');
        }

        $ssoIdentity->setProvider($provider);
        $ssoIdentity->setIdentifier($identifier);
        $ssoIdentity->setProfileData($profileData);
        $ssoIdentity->setPublished(true);
        $ssoIdentity->setKey($key);
        $ssoIdentity->setParent($user);

        return $ssoIdentity;
    }

    public function saveIdentity(SsoIdentityInterface $ssoIdentity): void
    {
        if (!$ssoIdentity instanceof DataObject\Concrete) {
            throw new \RuntimeException(sprintf('Identity needs to be instance of %s', DataObject\Concrete::class));
        }

        $ssoIdentity->save();
    }

    public function findExpiredSsoIdentities(int $ttl = 0): array
    {
        $ssoIdentityListing = $this->classManager->getSsoIdentityListing();

        if ($ttl === 0) {
            $ssoIdentityListing->addConditionParam('expiresAt IS NOT NULL AND expiresAt < ?', time());
        } else {
            $query = sprintf('o_creationDate < (UNIX_TIMESTAMP() - %s)', $ttl);
            $ssoIdentityListing->addConditionParam($query);
        }

        return $ssoIdentityListing->getObjects();
    }

    protected function findSsoIdentity(string $provider, string $identifier): ?SsoIdentityInterface
    {
        $ssoIdentityListing = $this->classManager->getSsoIdentityListing();

        $ssoIdentityListing->addConditionParam('provider = ?', $provider);
        $ssoIdentityListing->addConditionParam('identifier = ?', $identifier);

        if ($ssoIdentityListing->count() === 1) {
            $currentElement = $ssoIdentityListing->current();

            return $currentElement instanceof SsoIdentityInterface ? $currentElement : null;
        }

        if ($ssoIdentityListing->count() > 1) {
            throw new \RuntimeException(
                sprintf('Ambiguous results: found more than one identity for %s:%s', $provider, $identifier)
            );
        }

        return null;
    }

    protected function findUserBySsoIdentity(SsoIdentityInterface $ssoIdentity): ?UserInterface
    {
        /** @var DataObject\Concrete $userClass */
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

        return null;
    }

    protected function assertSsoAwareUser(UserInterface $user): void
    {
        if (!$user instanceof SsoAwareUserInterface) {
            throw new \RuntimeException('User needs to implement SsoAwareUserInterface');
        }
    }
}
