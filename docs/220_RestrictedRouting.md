# Restricted Routing

The MembersBundle tries to protect your documents by default. For that an event listener will watch every request.

1. If the route is not protected, the restriction will be skipped
2. If the route is protected and the user is not logged in and not allowed to open the page, he will get redirected via the `members_user_security_login` route.
3. If the route is protected and the user is logged in but has not the right privileges, he will get redirected via the `members_user_restriction_refused` route. 

## Static Route Routing
In some cases, objects are bounded to the view. For example a news, blog or a product object. In that case you probably added a static route (www.site.com/news/your-news).
Even if the object has a restriction, the view will not notice it and the user would be able to open the view. Because Members cannot detect the related object based on a static route, you need to take care about that. 

There is simple event listener you need to call: `StaticRouteEvent`

### 1. Service
First, create a service in your `config/services.yaml`:

```yaml
app.event_listener.members.restriction.staticroute:
    class: App\EventListener\MembersStaticRouteListener
    tags:
        - { name: kernel.event_subscriber }
```

### 2. Listener
Second, create an event listener:

```php
<?php

namespace App\EventListener;

use MembersBundle\Event\StaticRouteEvent;
use MembersBundle\MembersEvents;
use Pimcore\Model\DataObject\News;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RestrictedStaticRouteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MembersEvents::RESTRICTION_CHECK_STATICROUTE => 'checkStaticRoute'
        ];
    }

    public function checkStaticRoute(StaticRouteEvent $event): void
    {
        $request = $event->getRequest();
        if($event->getRouteName() === 'news_detail') {
            $newsObject = News::getById($request->attributes->get('newsId'));
            if($newsObject instanceof News) {
                //bind your object to the event. that's it.
                $event->setStaticRouteObject($newsObject);
            }
        }
    }
}
```
