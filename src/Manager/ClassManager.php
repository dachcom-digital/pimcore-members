<?php

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Tool;

class ClassManager implements ClassManagerInterface
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function getGroupListing(): Listing
    {
        /** @var AbstractObject $listingClass */
        $listingClass = $this->getGroupClass();

        if (!Tool::classExists($listingClass)) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    public function getUserListing(): Listing
    {
        /** @var AbstractObject $listingClass */
        $listingClass = $this->getUserClass();

        if (!Tool::classExists($listingClass)) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    public function getSsoIdentityListing(): Listing
    {
        /** @var AbstractObject $listingClass */
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
