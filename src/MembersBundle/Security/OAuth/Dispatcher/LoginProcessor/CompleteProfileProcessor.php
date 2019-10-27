<?php

namespace MembersBundle\Security\OAuth\Dispatcher\LoginProcessor;

use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Registry\OAuthLoginProcessorRegistryInterface;
use MembersBundle\Security\OAuth\Exception\AccountNotLinkedException;
use MembersBundle\Security\OAuth\OAuthRegistrationHandler;
use MembersBundle\Security\OAuth\OAuthResponse;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class CompleteProfileProcessor implements LoginProcessorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $authRegistrationHandler;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var OAuthRegistrationHandler
     */
    protected $oAuthHandler;

    /**
     * @var OAuthLoginProcessorRegistryInterface
     */
    protected $loginProcessorRegistry;

    /**
     * @param EventDispatcherInterface             $eventDispatcher
     * @param Configuration                        $configuration
     * @param OAuthRegistrationHandler             $authRegistrationHandler
     * @param UserManagerInterface                 $userManager
     * @param OAuthRegistrationHandler             $oAuthHandler
     * @param OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Configuration $configuration,
        OAuthRegistrationHandler $authRegistrationHandler,
        UserManagerInterface $userManager,
        OAuthRegistrationHandler $oAuthHandler,
        OAuthLoginProcessorRegistryInterface $loginProcessorRegistry
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->configuration = $configuration;
        $this->authRegistrationHandler = $authRegistrationHandler;
        $this->userManager = $userManager;
        $this->oAuthHandler = $oAuthHandler;
        $this->loginProcessorRegistry = $loginProcessorRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function process(string $provider, OAuthResponse $oAuthResponse)
    {
        try {
            $registrationKey = Uuid::uuid4();
        } catch (\Throwable $e) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Error while generating uuid. error was: %s', $e->getMessage())
            );
        }

        $user = $oAuthResponse->getResourceOwner();

        $this->authRegistrationHandler->saveToken($registrationKey, $oAuthResponse);

        $exception = new AccountNotLinkedException(sprintf(
            'No customer was found for user "%s" on provider "%s"',
            $user->getId(), $provider
        ));

        $exception->setRegistrationKey($registrationKey->toString());

        throw $exception;
    }
}
