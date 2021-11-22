# SSO Identity Listeners
If you need to hook into core decisions, you may want to use som identity state listener:

## OAUTH_IDENTITY_STATUS_DELETION
Use this event to change the deletion status of a SSO Identity.
This event triggers, if you have enabled the [identity clean-up task](./31_Listener.md).

```php
<?php

namespace App\EventListener;

use MembersBundle\MembersEvents;
use MembersBundle\Event\OAuth\OAuthIdentityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IdentityStatusDeletionEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::OAUTH_IDENTITY_STATUS_DELETION => 'onDispatch'
        ];
    }

    public function onDispatch(OAuthIdentityEvent $event): void
    {
        $user = $event->getIdentity();
   
        // this is just an example
        if (!empty($user->getLastLogin())) {
            // this will prevent the user deletion
            $event->setIdentityDispatchStatus(false);
        }
    }
}
```

## OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION
Use this event to change the status definition if a sso only user is able to call the completion profile route. 
By default, an instantly logged in user can complete his profile afterwards in his profile (`/en/members/profile/`, **if no password** has been set.
If you want to change that, use the `OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION` event:

```php
<?php

namespace App\EventListener;

use MembersBundle\MembersEvents;
use MembersBundle\Event\OAuth\OAuthIdentityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IdentityStatusProfileCompletionEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::OAUTH_IDENTITY_STATUS_PROFILE_COMPLETION => 'onDispatch'
        ];
    }

    public function onDispatch(OAuthIdentityEvent $event): void
    {
        $user = $event->getIdentity();
   
        // this is just an example
        if (!empty($user->getUsername())) {
            // this will prevent the user deletion
            $event->setIdentityDispatchStatus(false);
        }
    }
}
```