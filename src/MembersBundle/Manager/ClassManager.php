<?php

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Tool;

class ClassManager implements ClassManagerInterface
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getGroupListing(): Listing
    {
        $listingClass = $this->getGroupClass();

        if (!Tool::classExists($listingClass)) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    public function getUserListing(): Listing
    {
        $listingClass = $this->getUserClass();

        if (!Tool::classExists($listingClass)) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    /**
     * {@inheritdoc}
     */
    public function getSsoIdentityListing(): Listing
    {
        $listingClass = $this->getSsoIdentityClass();

        if (!Tool::classExists($listingClass)) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
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
