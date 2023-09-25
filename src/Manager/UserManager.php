<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;

class UserManager implements UserManagerInterface
{
    protected Configuration $configuration;
    protected ClassManagerInterface $classManager;
    protected int $memberStorageId;

    public function __construct(Configuration $configuration, ClassManagerInterface $classManager)
    {
        $this->configuration = $configuration;
        $this->classManager = $classManager;

        $storagePath = $configuration->getConfig('storage_path');
        if (($membersStoreObject = DataObject::getByPath($storagePath)) instanceof DataObject\Folder) {
            $this->memberStorageId = $membersStoreObject->getId();
        }
    }

    public function getClass(): string
    {
        return $this->classManager->getUserClass();
    }

    public function deleteUser(UserInterface $user): void
    {
        if (!$user instanceof DataObject\Concrete) {
            return;
        }

        $user->delete();
    }

    public function findUserByConfirmationToken(string $token, bool $includeUnpublished = true): ?UserInterface
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('confirmationToken = ?', [$token]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->getObjects();

        if (count($elements) === 0) {
            return null;
        }

        return $elements[0];
    }

    public function findUserByEmail(string $emailAddress, bool $includeUnpublished = true): ?UserInterface
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('email = ?', [$emailAddress]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->getObjects();

        if (count($elements) === 0) {
            return null;
        }

        return $elements[0];
    }

    public function findUserByUsername(string $username, bool $includeUnpublished = true): ?UserInterface
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('userName = ?', [$username]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->getObjects();

        if (count($elements) === 0) {
            return null;
        }

        return $elements[0];
    }

    public function findUserById(int $userId, bool $includeUnpublished = true): ?UserInterface
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('oo_id = ?', [$userId]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->getObjects();

        if (count($elements) === 0) {
            return null;
        }

        return $elements[0];
    }

    public function findUserByCondition(
        string $condition = '',
        array $conditionVariables = [],
        bool $includeUnpublished = true,
        bool $returnSingle = true
    ): ?UserInterface {

        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition($condition, $conditionVariables);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->getObjects();

        if (count($elements) === 0) {
            return null;
        }

        if (count($elements) > 1) {
            throw new \Exception(sprintf('User condition "%s" is ambiguous, multiple matches occurred', $condition));
        }

        return $elements[0];
    }

    public function findUserByUsernameOrEmail($usernameOrEmail): ?UserInterface
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    public function findUsers(): array
    {
        return $this->classManager->getUserListing()->getObjects();
    }

    public function reloadUser(UserInterface $user): void
    {
        throw new \Exception('reload user not implemented');
    }

    public function createUser(): UserInterface
    {
        $userClass = $this->classManager->getUserClass();

        return new $userClass();
    }

    public function createAnonymousUser(string $key): UserInterface
    {
        $userClass = $this->classManager->getUserClass();
        $user = new $userClass();

        return $this->setupNewUser($user, $key);
    }

    public function updateUser(UserInterface $user, array $properties = []): UserInterface
    {
        $new = false;

        $userConfig = $this->configuration->getConfig('user');
        $objectKeyGetter = sprintf('get%s', ucfirst($userConfig['adapter']['object_key_form_field']));

        //It's a new user!
        if (empty($user->getKey())) {
            $new = true;
            $key = $user->$objectKeyGetter();
            $user = $this->setupNewUser($user, $key);
        }

        // update user properties.
        if (!empty($properties)) {
            foreach ($properties as $propKey => $propValue) {
                $user->setProperty($propKey, 'text', $propValue, false);
            }
        }

        // Transfer plain password after a fresh register or a password reset action.
        if (!empty($user->getPlainPassword())) {
            $user->setPassword($user->getPlainPassword());
        }

        return $new ? $this->saveWithVersion($user) : $this->saveWithoutVersion($user);
    }

    private function setupNewUser(UserInterface $user, ?string $key): UserInterface
    {
        $validKey = $key ?? $user->getEmail();

        $user->setKey(\Pimcore\File::getValidFilename($validKey));
        $user->setParentId($this->memberStorageId);

        $userGroups = [];
        $userConfiguration = $this->configuration->getConfig('user');
        foreach ($userConfiguration['initial_groups'] as $group) {
            $listing = $this->classManager->getGroupListing();
            $listing->setUnpublished(false);

            if (is_string($group)) {
                $listing->setCondition('name = ?', [$group]);
            } else {
                $listing->setCondition('oo_id = ?', [$group]);
            }

            $objects = $listing->getObjects();
            if (count($objects) > 0) {
                $userGroups[] = $objects[0];
            }
        }

        if (count($userGroups)) {
            $user->setGroups($userGroups);
        }

        return $user;
    }

    /**
     * @throws \Exception
     */
    private function saveWithVersion(UserInterface $user): UserInterface
    {
        return $user->save();
    }

    /**
     * @throws \Exception
     */
    private function saveWithoutVersion(UserInterface $user): UserInterface
    {
        Version::disable();
        $state = $user->save();
        Version::enable();

        return $state;
    }
}
