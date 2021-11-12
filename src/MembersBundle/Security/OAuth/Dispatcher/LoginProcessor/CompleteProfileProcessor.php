<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use Symfony\Component\Uid\Uuid;
use MembersBundle\Security\OAuth\OAuthResponse;
use MembersBundle\Security\OAuth\OAuthTokenStorageInterface;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class CompleteProfileProcessor implements LoginProcessorInterface
{
    protected OAuthTokenStorageInterface $oAuthTokenStorage;

    public function __construct(OAuthTokenStorageInterface $oAuthTokenStorage)
    {
        $this->oAuthTokenStorage = $oAuthTokenStorage;
    }

    public function process(string $provider, OAuthResponse $oAuthResponse): void
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
