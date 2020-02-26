# Resource Mapping
If a user successfully gets connected via a given provider, you're able to map some data to your user entity.
For example, if you're using a google client, there are some properties you may wanna use (`email`, `name`).

Since every client may vary in naming of properties, you also may want to adjust the mapping. Just use a EventListener to do so.

## Default Mapping
If there is no registered event listener, Members tries to map some default fields by guessing all properties by `method_exists()`.
There are some invalid properties: 'lastLogin', 'password', 'confirmationToken', 'passwordRequestedAt', 'groups', 'ssoIdentities'.

However, if you're adding your own EventListener, the mapping is totally up to you.
There are two different types of mapping events:

### OAUTH_RESOURCE_MAPPING_PROFILE
The `OAUTH_RESOURCE_MAPPING_PROFILE` event occurs before a SSO Identity gets assigned to given user profile.
This Event fires shortly before the SSO Identity gets applied to the user. 

> **Attention**: This event also fires after an existing user gets connected to a client. You may don't want to override existing values! 

### OAUTH_RESOURCE_MAPPING_REGISTRATION
The `OAUTH_RESOURCE_MAPPING_REGISTRATION` event occurs before the registration form gets rendered 
(Only available if integration type is `complete_profile`).

## Adding EventListener
First, add your service:

```php
<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Event\OAuth\OAuthResourceEvent;
use MembersBundle\MembersEvents;

class MembersResourceMappingListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MembersEvents::OAUTH_RESOURCE_MAPPING_PROFILE      => 'onProfileMapping',
            MembersEvents::OAUTH_RESOURCE_MAPPING_REGISTRATION => 'onRegistrationMapping'
        ];
    }

    public function onProfileMapping(OAuthResourceEvent $event)
    {
        $user = $event->getUser();
        $resourceOwner = $event->getResourceOwner();
        $ownerDetails = $resourceOwner->toArray();

        $this->mapData($user, $ownerDetails);
    }

    public function onRegistrationMapping(OAuthResourceEvent $event)
    {
        $user = $event->getUser();
        $resourceOwner = $event->getResourceOwner();
        $ownerDetails = $resourceOwner->toArray();

        $this->mapData($user, $ownerDetails);
    }

    protected function mapData(UserInterface $user, array $ownerDetails)
    {
        if (empty($user->getEmail()) && isset($ownerDetails['email'])) {
            $user->setEmail($ownerDetails['email']);
        }

        if (empty($user->getUserName()) && isset($ownerDetails['name'])) {
            $user->setUserName($ownerDetails['name']);
        }
    }
}
```

Then, register it:

```yaml
AppBundle\EventListener\MembersResourceMappingListener:
    autowire: true
    tags:
        - { name: kernel.event_subscriber }
```