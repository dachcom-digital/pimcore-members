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

namespace MembersBundle\CoreExtension\Provider;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;

class RoleOptionsProvider implements SelectOptionsProviderInterface
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
        $roles = [$this->getDefaultValue($context, $fieldDefinition)];

        foreach ($this->originalRoles as $originalRole => $inheritedRoles) {
            array_push($roles, $originalRole, ...$inheritedRoles);
        }

        return array_map(
            static fn ($role): array => ['key' => $role, 'value' => $role],
            array_unique($roles)
        );
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
