<?php

namespace MembersBundle\CoreExtension\Provider;

use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\MultiSelectOptionsProviderInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Psr\Container\ContainerInterface;

class RoleOptionsProvider implements MultiSelectOptionsProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $originalRoles;

    /**
     * @var array
     */
    protected $invalidRoles = [
        'ROLE_PIMCORE_ADMIN'
    ];

    /**
     * RoleOptionsProvider constructor.
     */
    public function __construct()
    {
        $systemRoles = \Pimcore::getKernel()->getContainer()->getParameter('security.role_hierarchy.roles');
        $this->originalRoles = array_diff_key($systemRoles, array_flip($this->invalidRoles));
    }

    /**
     * @param array $context
     * @param Data  $fieldDefinition
     *
     * @return array
     */
    public function getOptions($context, $fieldDefinition)
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

    /**
     * @param array $context
     * @param Data  $fieldDefinition
     *
     * @return bool
     */
    public function hasStaticOptions($context, $fieldDefinition)
    {
        return false;
    }

    /**
     * @param array $context
     * @param Data  $fieldDefinition
     *
     * @return mixed
     */
    public function getDefaultValue($context, $fieldDefinition)
    {
        return 'ROLE_USER';
    }
}
