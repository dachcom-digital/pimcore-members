<?php

namespace MembersBundle\CoreExtension\Provider;

use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\MultiSelectOptionsProviderInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;

class RoleOptionsProvider implements MultiSelectOptionsProviderInterface
{
    protected array $originalRoles;
    protected array $invalidRoles = [
        'ROLE_PIMCORE_ADMIN'
    ];

    public function __construct(array $systemRoles)
    {
        $this->originalRoles = array_diff_key($systemRoles, array_flip($this->invalidRoles));
    }

    public function getOptions(array $context, Data $fieldDefinition): array
    {
        $roles = [];

        /*
         * Get all unique roles
         */
        foreach ($this->originalRoles as $originalRole => $inheritedRoles) {
            foreach ($inheritedRoles as $inheritedRole) {
                $roles[] = $inheritedRole;
            }

            $roles[] = $originalRole;
        }

        $result = [];

        foreach (array_unique($roles) as $role) {
            $result[] = ['key' => $role, 'value' => $role];
        }

        return $result;
    }

    public function hasStaticOptions(array $context, Data $fieldDefinition): bool
    {
        return false;
    }

    public function getDefaultValue(array $context, Data $fieldDefinition): ?string
    {
        return 'ROLE_USER';
    }
}
