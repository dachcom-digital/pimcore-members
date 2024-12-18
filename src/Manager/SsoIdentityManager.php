<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Manager;

use Doctrine\DBAL\Connection;
use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\SsoAwareUserInterface;
use MembersBundle\Adapter\User\UserInterface;
use Pimcore\File;
use Pimcore\Model\DataObject;

class SsoIdentityManager implements SsoIdentityManagerInterface
{
    public function __construct(
        protected Connection $connection,
        protected UserManagerInterface $userManager,
        protected ClassManagerInterface $classManager
    ) {
    }

    public function getClass(): string
    {
        return $this->classManager->getSsoIdentityClass();
    }

    public function getUserBySsoIdentity(string $provider, string $identifier): ?UserInterface
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

        return $user->getSsoIdentities();
    }

    /**
     * {@inheritdoc}
     */
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

        $ssoIdentity = DataObject::getByPath($path);

        if (!$ssoIdentity instanceof SsoIdentityInterface) {
            $ssoIdentityClass = $this->classManager->getSsoIdentityClass();
            $ssoIdentity = new $ssoIdentityClass();
        }

        $ssoIdentity->setProvider($provider);
        $ssoIdentity->setIdentifier($identifier);
        $ssoIdentity->setProfileData($profileData);

        if ($ssoIdentity instanceof DataObject\Concrete) {
            $ssoIdentity->setPublished(true);
            $ssoIdentity->setKey($key);
            $ssoIdentity->setParent($user);
        }

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

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    protected function assertSsoAwareUser(UserInterface $user): void
    {
        if (!$user instanceof SsoAwareUserInterface) {
            throw new \RuntimeException('User needs to implement SsoAwareUserInterface');
        }
    }
}
