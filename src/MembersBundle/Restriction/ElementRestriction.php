<?php

namespace MembersBundle\Restriction;

use MembersBundle\Manager\RestrictionManager;

class ElementRestriction
{
    /**
     * @var integer
     */
    public $state = RestrictionManager::RESTRICTION_STATE_NOT_LOGGED_IN;

    /**
     * @var string
     */
    public $section = RestrictionManager::RESTRICTION_SECTION_NOT_ALLOWED;

    /**
     * @var array
     */
    public $restrictionGroups = [];

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $section
     *
     * @return $this
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return array
     */
    public function getRestrictionGroups()
    {
        return $this->restrictionGroups;
    }

    /**
     * @param array $groups
     *
     * @return $this
     */
    public function setRestrictionGroups($groups = [])
    {
        $this->restrictionGroups = $groups;
        return $this;
    }
}
