<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use Pimcore\Model\Listing\AbstractListing;
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
     * userManager constructor.
     *
     * @param Configuration         $configuration
     * @param ClassManagerInterface $classManager
     */
    public function __construct(Configuration $configuration, ClassManagerInterface $classManager)
    {
        $this->configuration = $configuration;
        $this->classManager = $classManager;
        $membersStorePath = DataObject::getByPath('/members');
        if($membersStorePath instanceof DataObject\Folder) {
            $this->memberStorageId = DataObject::getByPath('/members')->getId();
        }
    }

    public function getClass()
    {
        return $this->classManager->getUserClass();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        return $user->delete();
    }

    /**
     * @param      $token
     * @param bool $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByConfirmationToken($token, $includeUnpublished = TRUE)
    {
        /** @var AbstractListing $memberListing */
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('confirmationToken = ?', [$token]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return NULL;
    }

    /**
     * @param      $emailAddress
     * @param bool $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByEmail($emailAddress, $includeUnpublished = TRUE)
    {
        /** @var AbstractListing $memberListing */
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('email = ?', [$emailAddress]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return NULL;
    }

    /**
     * @fixme: includeUnpublished?
     *
     * @param  string $username
     * @param bool    $includeUnpublished
     *
     * @return NULL|UserInterface
     */
    public function findUserByUsername($username, $includeUnpublished = TRUE)
    {
        /** @var AbstractListing $memberListing */
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition('userName = ?', [$username]);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByCondition($condition = '', $conditionVariables = [], $includeUnpublished = TRUE)
    {
        /** @var AbstractListing $memberListing */
        $memberListing = $this->classManager->getUserListing();
        $memberListing->setCondition($condition, $conditionVariables);
        $memberListing->setUnpublished($includeUnpublished);

        $elements = $memberListing->load();

        if (count($elements) === 1) {
            return $elements[0];
        }

        return NULL;
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
        /** @var AbstractListing $memberListing */
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
     * @param UserInterface $user
     * @param array         $properties
     *
     * @return mixed
     */
    public function updateUser(UserInterface $user, $properties = [])
    {
        $new = FALSE;

        //It's a new user!
        if (empty($user->getKey())) {
            $new = TRUE;
            $user = $this->setupNewUser($user);
        }

        // update page properties.
        if (!empty($properties)) {
            foreach ($properties as $propKey => $propValue) {
                $user->setProperty($propKey, 'text', $propValue, FALSE);
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
            $listing->setUnpublished(FALSE);

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

        $user->setGroups($userGroups);

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