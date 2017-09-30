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
     * ClassManager constructor.
     *
     * @param $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
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

    /**
     * @return bool|string
     */
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