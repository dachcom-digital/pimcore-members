<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Password;

trait UserTrait
{
    /**
     * The salt to use for hashing.
     *
     * @var string
     */
    protected $salt;

    /**
     * Plain password. Used for model validation.
     * Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        // user has no salt as we use password_hash
        // which handles the salt by itself
        return null;
    }

    /**
     * Trigger the hash calculation to remove the plain text password from the instance. This
     * is necessary to make sure no plain text passwords are serialized.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        /** @var Password $field */
        $field = $this->getClass()->getFieldDefinition('password');
        $field->getDataForResource($this->getPassword(), $this);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;

        /** @var GroupInterface $group */
        foreach ($this->getGroups() as $group) {
            $groupRoles = $group->getRoles();
            $roles = array_merge($roles, is_array($groupRoles) ? $groupRoles : []);
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames(): array
    {
        $names = [];
        /** @var GroupInterface $group */
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated UserTrait::hasGroup is deprecated and will be removed with 4.0,
     * please use either UserTrait::hasGroupId or UserTrait::hasGroupName instead.
     */
    public function hasGroup($name)
    {
        trigger_error(
            sprintf(
                '%s::%s is deprecated and will be removed with 4.0, please use either %s::hasGroupId or %s::hasGroupName instead.',
                static::class,
                __METHOD__,
                static::class,
                static::class
            ),
            E_USER_DEPRECATED
        );

        return in_array($name, $this->getGroupNames());
    }

    /**
     * Checks if a certain group is assigned to the user by comparing ID's.
     *
     * @param GroupInterface $userGroup
     *
     * @return bool
     */
    public function hasGroupId(GroupInterface $userGroup): bool
    {
        $groups = $this->getGroups() ?? [];
        $groupIds = array_map(static function ($group) {
            /* @var GroupInterface $group */
            return $group->getId();
        }, $groups);

        return in_array($userGroup->getId(), $groupIds, true);
    }

    /**
     * Checks if a certain group is assigned to the user by comparing group names.
     *
     * @param GroupInterface $userGroup
     *
     * @return bool
     */
    public function hasGroupName(GroupInterface $userGroup): bool
    {
        return in_array($userGroup->getName(), $this->getGroupNames(), true);
    }

    /**
     * Adds a group to the user.
     *
     * @param GroupInterface $userGroup
     */
    public function addGroup(GroupInterface $userGroup): void
    {
        $groups = $this->getGroups() ?? [];
        $groups[] = $userGroup;

        $this->setGroups($groups);
    }

    /**
     * Removes a group from the user.
     *
     * @param GroupInterface $userGroup
     */
    public function removeGroup(GroupInterface $userGroup): void
    {
        $groups = $this->getGroups() ?? [];
        $groupIds = array_map(static function ($group) {
            /* @var GroupInterface $group */
            return $group->getId();
        }, $groups);

        if (($key = array_search($userGroup->getId(), $groupIds, true)) !== false) {
            unset($groups[$key]);
        }

        $this->setGroups($groups);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \Carbon\Carbon && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
