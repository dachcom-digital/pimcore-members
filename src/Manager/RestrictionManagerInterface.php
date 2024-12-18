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

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\Element\ElementInterface;

interface RestrictionManagerInterface
{
    public function getElementRestrictedGroups(ElementInterface $element): array;

    public function getElementRestrictionStatus(ElementInterface $element): ElementRestriction;

    public function isFrontendRequestByAdmin(): bool;

    public function elementIsInProtectedStorageFolder(ElementInterface $element): bool;

    public function pathIsInProtectedStorageFolder(string $path): bool;

    public function getUser(): ?UserInterface;
}
