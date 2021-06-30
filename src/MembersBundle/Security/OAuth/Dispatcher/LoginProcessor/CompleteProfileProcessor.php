<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthTokenStorageInterface;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Uid\Uuid;

class CompleteProfileProcessor implements LoginProcessorInterface
{
    protected OAuthTokenStorageInterface $oAuthTokenStorage;

    public function __construct(OAuthTokenStorageInterface $oAuthTokenStorage)
    {
        $this->oAuthTokenStorage = $oAuthTokenStorage;
    }

    public function process(string $provider, OAuthResponse $oAuthResponse): ?UserInterface
    {
        try {
            $registrationKey = Uuid::v4();
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

        $exception->setRegistrationKey($registrationKey->toString());

        throw $exception;
    }
}
