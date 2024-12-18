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

namespace MembersBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SystemExtension extends AbstractExtension
{
    public function __construct(protected bool $oauthEnabled)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_system_oauth_enabled', [$this, 'oauthIsEnabled']),
        ];
    }

    public function oauthIsEnabled(): bool
    {
        return $this->oauthEnabled;
    }
}
