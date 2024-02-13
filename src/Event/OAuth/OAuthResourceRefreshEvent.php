<?php

namespace MembersBundle\Event\OAuth;

class OAuthResourceRefreshEvent extends OAuthResourceEvent
{
    protected bool $hasChanged = false;

    public function hasChanged(): bool
    {
        return $this->hasChanged === true;
    }

    public function setHasChanged(bool $hasChanged): void
    {
        $this->hasChanged = $hasChanged;
    }
}
