<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;

class UserManager implements UserManagerInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var ClassManagerInterface
     */
    protected $classManager;

    /**
     * @var int
     */
    protected $memberStorageId;

    /**
     * UserManager constructor.
     *
     * @param Configuration         $configuration
     * @param ClassManagerInterface $classManager
     */
    public function __construct(Configuration $configuration, ClassManagerInterface $classManager)
    {
        $this->configuration = $configuration;
        $this->classManager = $classManager;

        $storagePath = $configuration->getConfig('storage_path');
        if (($membersStoreObject = DataObject::getByPath($storagePath)) instanceof DataObject\Folder) {
            $this->memberStorageId = $membersStoreObject->getId();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->classManager->getUserClass();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        if (!$user instanceof DataObject\Concrete) {
            return false;
        }

        return $user->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token, $includeUnpublished = true)
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('confirmationToken = ?', [$token]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($emailAddress, $includeUnpublished = true)
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('email = ?', [$emailAddress]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username, $includeUnpublished = true)
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('userName = ?', [$username]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByCondition($condition = '', $conditionVariables = [], $includeUnpublished = true, $returnSingle = true)
    {
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition($condition, $conditionVariables);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) > 0) {
            return $returnSingle ? $elements[0] : $elements;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsers()
    {
        $memberListing = $this->classManager->getUserListing();

        return $memberListing->load();
    }

    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user)
    {
        throw new \Exception('reload user not implemented');
    }

    /**
     * @return UserInterface
     */
    public function createUser()
    {
        $userClass = $this->classManager->getUserClass();
        $user = new $userClass();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $properties = [])
    {
        $new = false;

        //It's a new user!
        if (empty($user->getKey())) {
            $new = true;
            $user = $this->setupNewUser($user);
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

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    private function setupNewUser(UserInterface $user)
    {
        $user->setKey(\Pimcore\File::getValidFilename($user->getEmail()));
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
        if(count($userGroups)) {
            $user->setGroups($userGroups);
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    private function saveWithVersion($user)
    {
        return $user->save();
    }

    /**
     * @param UserInterface $user
     *
     * @return mixed
     */
    private function saveWithoutVersion($user)
    {
        Version::disable();
        $state = $user->save();
        Version::enable();

        return $state;
    }
}
