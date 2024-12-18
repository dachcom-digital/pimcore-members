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

namespace MembersBundle\Security\OAuth\Dispatcher;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Security\OAuth\OAuthResponse;

class LoginDispatcher implements DispatcherInterface
{
    public function __construct(
        protected Configuration $configuration,
        protected OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
    ) {
    }

    public function dispatch(string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        $activationStrategyName = $this->configuration->getOAuthConfig('activation_type');

        if ($this->loginProcessorRegistry->has($activationStrategyName) === false) {
            throw new \Exception(sprintf('no dispatcher with identifier %s found', $provider));
        }

        return $this->loginProcessorRegistry->get($activationStrategyName)->process($provider, $oAuthResponse);
    }
}
