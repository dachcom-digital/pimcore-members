<?php

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;

class ClassManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * ClassManager constructor.
     *
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    public function getGroupListing()
    {
        $className = $this->configuration->getConfig('group');

        if (empty($className['adapter']['class_name'])) {
            return FALSE;
        }

        $listing = 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);

        if (!\Pimcore\Tool::classExists($listing)) {
            return FALSE;
        }

        return $listing::getList();
    }

    public function getUserListing()
    {
        $className = $this->configuration->getConfig('user');

        if (empty($className['adapter']['class_name'])) {
            return FALSE;
        }

        $listing = 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);

        if (!\Pimcore\Tool::classExists($listing)) {
            return FALSE;
        }

        return $listing::getList();
    }

    public function getUserClass()
    {
        $className = $this->configuration->getConfig('user');

        if (empty($className['adapter']['class_name'])) {
            return FALSE;
        }

        $class = 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);

        return $class;
    }
}