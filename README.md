# Pimcore Members

Just download and install it into your plugin folder.

### Requirements
* Pimcore 4.0

### Features
* Create Members in Backend
* Allow Members to register in frontend
* Restrict Documents to specific User Roles

### Hints

**Hide restricted pages in navigation**

Pimcore does not allow to manipulate the navigation globally without caching it.
Members provides some helpers to fix that. Use your *pimcoreNavigation* like that:

```php
<?= $this->pimcoreNavigation(

    $this->document,
    $mainNavStartNode,
    null,
    function ($page, $document) {
        Members\Tool\Tool::bindRestrictionToNavigation( $document, $page );
    },
    Members\Tool\Tool::generateNavCacheKey()
);
?>
```