<?php

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject\Listing;

class ClassManager implements ClassManagerInterface
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getGroupListing(): Listing
    {
        $groupClass = $this->getGroupClass();

        return $groupClass::getList();
    }

    public function getUserListing(): Listing
    {
        $listing = $this->getUserClass();

        return $listing::getList();
    }

    public function getSsoIdentityListing(): Listing
    {
        $listing = $this->getSsoIdentityClass();

        return $listing::getList();
    }

    public function getGroupClass(): string
    {
        $className = $this->configuration->getConfig('group');
        if (empty($className['adapter']['class_name'])) {
            return '';
        }

        return 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);
    }

    public function getUserClass(): string
    {
        $className = $this->configuration->getConfig('user');

        if (empty($className['adapter']['class_name'])) {
            return '';
        }

        return 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);
    }

    public function getSsoIdentityClass(): string
    {
        $className = $this->configuration->getConfig('sso');

        if (empty($className['adapter']['class_name'])) {
            return '';
        }

        return 'Pimcore\\Model\\DataObject\\' . ucfirst($className['adapter']['class_name']);
    }
}
