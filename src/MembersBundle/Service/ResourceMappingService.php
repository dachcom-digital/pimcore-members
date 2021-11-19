<?php

namespace MembersBundle\Service;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthResourceEvent;
use MembersBundle\MembersEvents;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as ComponentEventDispatcherInterface;

class ResourceMappingService
{
    public const MAP_FOR_PROFILE = 'profile';
    public const MAP_FOR_REGISTRATION = 'registration';

    protected string $authIdentifier;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(string $authIdentifier, EventDispatcherInterface $eventDispatcher)
    {
        $this->authIdentifier = $authIdentifier;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Exception
     */
    public function mapResourceData(UserInterface $user, ResourceOwnerInterface $resourceOwner, string $type): void
    {
        $eventIdentifier = sprintf('OAUTH_RESOURCE_MAPPING_%s', strtoupper($type));
        $eventPath = sprintf('%s::%s', MembersEvents::class, $eventIdentifier);

        if (!defined($eventPath)) {
            throw new \Exception(sprintf('OAuth Resource Event "%s" does not exist.', $eventIdentifier));
        }

        $eventName = constant($eventPath);
        if ($this->eventDispatcher instanceof ComponentEventDispatcherInterface && $this->eventDispatcher->hasListeners($eventName) === false) {
            $this->addDefaults($user, $resourceOwner, $type);

            return;
        }

        $this->eventDispatcher->dispatch(new OAuthResourceEvent($user, $resourceOwner), $eventName);
    }

    public function addDefaults(UserInterface $user, ResourceOwnerInterface $resourceOwner, string $type): void
    {
        $ownerDetails = $resourceOwner->toArray();
        $disallowedProperties = ['lastLogin', 'password', 'confirmationToken', 'passwordRequestedAt', 'groups', 'ssoIdentities'];

        if (!is_array($ownerDetails)) {
            return;
        }

        foreach ($ownerDetails as $property => $value) {
            if (in_array(strtolower($property), $disallowedProperties)) {
                continue;
            }

            $this->setIfEmpty($user, $property, $value);
        }

        // we NEED a valid property for UserInterface::getUserIdentifier() which requires a string as return value
        // since there is almost never a given username via OAuthResponse, we need to set username as an empty string!
        if ($this->authIdentifier === 'username' && empty($user->getUserName())) {
            $user->setUserName('');
        }
    }

    protected function setIfEmpty(UserInterface $user, string $property, mixed $value = null): void
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);

        if (!method_exists($user, $getter)) {
            return;
        }

        if (!method_exists($user, $setter)) {
            return;
        }

        if (!empty($value) && empty($user->$getter())) {
            $user->$setter($value);
        }
    }
}
