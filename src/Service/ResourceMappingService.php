<?php

namespace MembersBundle\Service;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthResourceEvent;
use MembersBundle\Event\OAuth\OAuthResourceRefreshEvent;
use MembersBundle\Exception\EntityNotRefreshedException;
use MembersBundle\MembersEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResourceMappingService
{
    public const MAP_FOR_PROFILE = 'profile';
    public const MAP_FOR_REGISTRATION = 'registration';
    public const MAP_FOR_REFRESH = 'refresh';

    public function __construct(
        protected string $authIdentifier,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws \Exception
     * @throws EntityNotRefreshedException
     */
    public function mapResourceData(UserInterface $user, ResourceOwnerInterface $resourceOwner, string $type): void
    {
        $eventIdentifier = sprintf('OAUTH_RESOURCE_MAPPING_%s', strtoupper($type));
        $eventPath = sprintf('%s::%s', MembersEvents::class, $eventIdentifier);

        if (!defined($eventPath)) {
            throw new \Exception(sprintf('OAuth Resource Event "%s" does not exist.', $eventIdentifier));
        }

        $eventName = constant($eventPath);
        if ($this->eventDispatcher instanceof EventDispatcherInterface && $this->eventDispatcher->hasListeners($eventName) === false) {
            $this->addDefaults($user, $resourceOwner, $type);

            return;
        }

        $eventClass = $type === self::MAP_FOR_REFRESH ? OAuthResourceRefreshEvent::class : OAuthResourceEvent::class;
        $event = new $eventClass($user, $resourceOwner);

        $this->eventDispatcher->dispatch($event, $eventName);

        if ($event instanceof OAuthResourceRefreshEvent && $event->hasChanged() === false) {
            throw new EntityNotRefreshedException(sprintf('entity %d has not changed', $user->getId()));
        }

    }

    public function addDefaults(UserInterface $user, ResourceOwnerInterface $resourceOwner, string $type): void
    {
        // do not add default values in refresh mode
        if ($type === self::MAP_FOR_REFRESH) {
            return;
        }

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
