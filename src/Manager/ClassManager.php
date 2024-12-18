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

namespace MembersBundle\Manager;

use MembersBundle\Configuration\Configuration;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Tool;

class ClassManager implements ClassManagerInterface
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function getGroupListing(): Listing
    {
        $listingClass = $this->getGroupClass();

        if (!Tool::classExists($listingClass) || !method_exists($listingClass, 'getList')) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    public function getUserListing(): Listing
    {
        $listingClass = $this->getUserClass();

        if (!Tool::classExists($listingClass) || !method_exists($listingClass, 'getList')) {
            throw new \Exception(sprintf('Cannot create listing with class "%s"', $listingClass));
        }

        return $listingClass::getList();
    }

    public function getSsoIdentityListing(): Listing
    {
        $listingClass = $this->getSsoIdentityClass();

        if (!Tool::classExists($listingClass) || !method_exists($listingClass, 'getList')) {
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
