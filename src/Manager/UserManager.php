<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\AbstractUser;
use MembersBundle\Adapter\User\UserInterface;
use Pimcore\Model\Listing\AbstractListing;
use Pimcore\Model\Object;

class UserManager implements UserManagerInterface
{
    /**
     * @var ClassManager
     */
    protected $classManager;

    /**
     * @var int
     */
    protected $memberStorageId;

    /**
     * userManager constructor.
     *
     * @param ClassManager $classManager
     */
    public function __construct($classManager)
    {
        $this->classManager = $classManager;
        $this->memberStorageId = Object::getByPath('/members')->getId();
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
     * @return NULL|AbstractUser
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
     * @return NULL|AbstractUser
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
     * @return NULL|AbstractUser
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

    public function updateUser(UserInterface $user)
    {
        //It's a new user!
        if (empty($user->getKey())) {
            $user->setKey(\Pimcore\File::getValidFilename($user->getEmail()));
            $user->setParentId($this->memberStorageId);
        }

        if(!empty($user->getPlainPassword())) {
            $user->setPassword($user->getPlainPassword());
        }

        return $user->save();
    }
}