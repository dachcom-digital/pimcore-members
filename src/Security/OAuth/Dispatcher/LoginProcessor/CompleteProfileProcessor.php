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

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthTokenStorageInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Uid\Uuid;

class CompleteProfileProcessor implements LoginProcessorInterface
{
    public function __construct(protected OAuthTokenStorageInterface $oAuthTokenStorage)
    {
    }

    public function process(string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        try {
            $registrationKey = (Uuid::v4())->toRfc4122();
        } catch (\Throwable $e) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Error while generating uuid. error was: %s', $e->getMessage())
            );
        }

        $user = $oAuthResponse->getResourceOwner();

        $this->oAuthTokenStorage->saveToken($registrationKey, $oAuthResponse);

        $exception = new AccountNotLinkedException(sprintf(
            'No user was found for user "%s" on provider "%s"',
            $user->getId(),
            $provider
        ));

        $exception->setRegistrationKey($registrationKey);

        throw $exception;
    }
}
