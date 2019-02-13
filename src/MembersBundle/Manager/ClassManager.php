<?php

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;

class ClassManager implements ClassManagerInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupListing()
    {
        $groupClass = $this->getGroupClass();
        if (!\Pimcore\Tool::classExists($groupClass)) {
            return false;
        }

        return $groupClass::getList();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserListing()
    {
        $listing = $this->getUserClass();
        if (!\Pimcore\Tool::classExists($listing)) {
            return false;
        }

        return $listing::getList();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupClass()
    {
        $className = $this->configuration->getConfig('group');
        if (empty($className['adapter']['class_name'])) {
            return '';
        }

        $class = 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserClass()
    {
        $className = $this->configuration->getConfig('user');

        if (empty($className['adapter']['class_name'])) {
            return '';
        }

        $class = 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);

        return $class;
    }
}
