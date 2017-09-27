# Use LuceneSearch with Members
Members works perfectly with the LuceneSearch Bundle. In fact, there are already two build-in events, which will append some data to the crawler:

1. `lucene_search.task.parser.asset_restriction`: This Event will add the asset restriction to the parser
2. `lucene_search.frontend.restriction_context`: This Event will add some restriction info to the frontend search.

## Authentication
But those events are useless unless you let the LuceneSearch Bundle know about it.
So, there is still work to do: To allow LuceneSearch to crawl all the restricted routes, you need to implement a guardian. Don't worry, it's very easy!

### Implement Guardian
Simple add the `MembersBundle\Security\LuceneSearchAuthenticator` service to your `app/config/config.yml`.
This authenticator comes with the Members Bundle, so there is nothing else to do. From now on, your system will watch every request for a lucene search crawl event.

> Feel free to add your custom authenticator if needed.

```yaml
security:
    firewalls:
        members_fe:
            guard:
                authenticators:
                    - MembersBundle\Security\LuceneSearchAuthenticator
```

### Add Authentication Information to Crawler Request
You're almost there. As a last step you need to pass the credentials to each crawler request, so the Members Guard can identify your crawler.

1. Create a Members User (for example _lucene_search_crawler_). Select all groups and save it.
2. Create a EventListener in `AppBundle\EventListener` (for example _LuceneSearchCrawlerHeader_)

```php
<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LuceneSearchBundle\Event\CrawlerRequestHeaderEvent;

class LuceneSearchCrawlerHeader implements EventSubscriberInterface
{
    protected $userName;
    protected $password;

    public function __construct($userName = NULL, $password = NULL)
    {
        $this->userName = $userName;
        $this->password = $password;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'lucene_search.task.crawler.request_header' => 'addAuthHeader'
        ];
    }

    public function addAuthHeader(CrawlerRequestHeaderEvent $event)
    {
        $event->addHeader([
            'name'       => 'x-lucene-search-authorization',
            'value'      => 'Basic ' . base64_encode($this->userName . ':' . $this->password),
            'identifier' => 'lucene-search-auth'
        ]);
    }
}
```

3. Register a EventListener in `app/config/config.yml`:

```yaml
# credentials of the new lucene search user
parameters:
    lucene_search_user_name: 'lucene_search_crawler'
    lucene_search_password: 'crawler@universe.org'

services:
    app.event_listener.lucene_search_crawler_header:
        class: AppBundle\EventListener\LuceneSearchCrawlerHeader
        arguments:
            - '%lucene_search_user_name%'
            - '%lucene_search_password%'
        tags:
            - { name: kernel.event_subscriber }
```

4. Done! Re-Run your crawler. All your restricted pages should be available in the index now. 
Don't worry, they are save since the Members Bundle automatically protects the user frontend search (check top of this page).