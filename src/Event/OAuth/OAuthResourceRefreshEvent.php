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
