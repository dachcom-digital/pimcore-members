<?php

namespace MembersBundle\Service;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthResourceEvent;
use MembersBundle\MembersEvents;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResourceMappingService
{
    public const MAP_FOR_PROFILE = 'profile';
    public const MAP_FOR_REGISTRATION = 'registration';

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function mapResourceData(UserInterface $user, ResourceOwnerInterface $resourceOwner, string $type): void
    {
        $eventIdentifier = sprintf('OAUTH_RESOURCE_MAPPING_%s', strtoupper($type));
        $eventPath = sprintf('%s::%s', MembersEvents::class, $eventIdentifier);

        if (!defined($eventPath)) {
            throw new \Exception(sprintf('OAuth Resource Event "%s" does not exist.', $eventIdentifier));
        }

        $eventName = constant($eventPath);
        if ($this->eventDispatcher->hasListeners($eventName) === false) {
            $this->addDefaults($user, $resourceOwner, $type);

            return;
        }

        $event = new OAuthResourceEvent($user, $resourceOwner);
        $this->eventDispatcher->dispatch($eventName, $event);
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
    }

    protected function setIfEmpty(UserInterface $user, string $property, $value = null)
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
