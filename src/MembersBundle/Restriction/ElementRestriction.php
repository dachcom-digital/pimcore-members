<?php

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
