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

namespace MembersBundle\Security\OAuth\Dispatcher\Router;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\Dispatcher\DispatcherInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

class DispatchRouter
{
    protected array $dispatcher = [];

    public function register(string $dispatcherName, DispatcherInterface $dispatcher): void
    {
        $this->dispatcher[$dispatcherName] = $dispatcher;
    }

    public function dispatch(string $dispatcherName, string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher[$dispatcherName];

        return $dispatcher->dispatch($provider, $oAuthResponse);
    }
}
