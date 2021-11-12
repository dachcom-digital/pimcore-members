# Restricted Navigation

Now - since you have restricted some documents to certain groups, you need to manipulate the pimcore navigation renderer.

**Navigation:** Do **not** use the default nav builder extension (`pimcore_build_nav`). Just use the `members_build_nav` to build
secure menus. Otherwise your restricted pages will show up. This twig extension will also handle your navigation cache strategy.

## Usage

```twig
{% set nav = members_build_nav({
    active: currentDoc,
    root: documentRootDoc
}) %}

{{ pimcore_render_nav(nav, 'menu', 'renderMenu', { maxDepth: 2 }) }}
```

### Page callback parameter

The `pageCallback` parameter is 'merged' with the Members bundle restriction access callback, allowing you to set custom data to
the navigation document. Note that the `ElementRestriction` argument is also passed on to the callback function.

```twig
{% set nav = members_build_nav({
    active: document,
    root: rootPage,
    pageCallback: navigation_callback()
}) %}
```

As you cannot use closures directly within Twig templates, create a function to return one.

```php
<?php declare(strict_types=1);

namespace App\Twig\Extension;

use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Document;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('navigation_callback', [$this, 'getNavigationCallback'])
        ];
    }

    public function getNavigationCallback(): \Closure
    {
        return function (
            \Pimcore\Navigation\Page\Document $document,
            AbstractModel $page,
            ElementRestriction $elementRestriction
        ): void {
            if ($page instanceof Document) {
                $document->setCustomSetting('key', $page->getKey());
            }
        };
    }
}
```

To retrieve the setting in the template:

```twig
{{ page.getCustomSetting('key') }}
```
