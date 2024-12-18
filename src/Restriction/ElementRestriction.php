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

namespace MembersBundle\Restriction;

use MembersBundle\Manager\RestrictionManager;

class ElementRestriction
{
    public string $state = RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN;
    public string $section = RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED;
    public array $restrictionGroups = [];

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getRestrictionGroups(): array
    {
        return $this->restrictionGroups;
    }

    public function setRestrictionGroups(array $groups = []): self
    {
        $this->restrictionGroups = $groups;

        return $this;
    }
}
