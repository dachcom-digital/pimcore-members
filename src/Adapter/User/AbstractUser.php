<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\Object\ClassDefinition\Data\Password;
use Pimcore\Model\Object\Concrete;

abstract class AbstractUser extends Concrete implements UserInterface
{
    /**
     * The salt to use for hashing.
     * @var string
     */
    protected $salt;

    /**
     * Plain password. Used for model validation. Must not be persisted.
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
     * @inheritDoc
     */
    public function getSalt()
    {
        // user has no salt as we use password_hash
        // which handles the salt by itself
        return NULL;
    }

    /**
     * Trigger the hash calculation to remove the plain text password from the instance. This
     * is necessary to make sure no plain text passwords are serialized.
     * @inheritDoc
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
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames()
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
     */
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
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
        return $this->getPasswordRequestedAt() instanceof \Carbon\Carbon &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
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
        return (string)$this->getUsername();
    }
}