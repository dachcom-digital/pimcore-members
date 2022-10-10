<?php

namespace MembersBundle\Adapter\User;

use MembersBundle\Adapter\Group\GroupInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Password;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserTrait
{
    private array $roles = [];

    /**
     * Plain password. Used for model validation.
     * Must not be persisted.
     */
    protected ?string $plainPassword = null;

    /**
     * This method is deprecated since Symfony 5.3
     *
     * @deprecated
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        $authIdentifier = \Pimcore::getContainer()?->getParameter('members.auth.identifier');

        return $authIdentifier === 'email' ? $this->getEmail() : $this->getUserName();
    }

    /**
     * Trigger the hash calculation to remove the plain text password from the instance. This
     * is necessary to make sure no plain text passwords are serialized.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $field = $this->getClass()?->getFieldDefinition('password');

        if (!$field instanceof Password) {
            throw new \Exception('Field "password" class not found');
        }

        $field->getDataForResource($this->getPassword(), $this);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user): bool
    {
         return $user instanceof self && $user->getId() === $this->getId();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        /** @var GroupInterface $group */
        foreach ($this->getGroups() as $group) {
            $groupRoles = $group->getRoles();
            $roles = array_merge($roles, is_array($groupRoles) ? $groupRoles : []);
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

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
     * Checks if a certain group is assigned to the user by comparing ID's.
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
     */
    public function hasGroupName(GroupInterface $userGroup): bool
    {
        return in_array($userGroup->getName(), $this->getGroupNames(), true);
    }

    /**
     * Adds a group to the user.
     */
    public function addGroup(GroupInterface $userGroup): void
    {
        $groups = $this->getGroups() ?? [];
        $groups[] = $userGroup;

        $this->setGroups($groups);
    }

    /**
     * Removes a group from the user.
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

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \Carbon\Carbon && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function isAccountNonExpired(): bool
    {
        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return true;
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
