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
        Members\Tool\Observer::bindRestrictionToNavigation($document, $page);
    },
    Members\Tool\Observer::generateNavCacheKey()
); ?>
```
**Use restriction on objects**

Just extend your object classes with `\Members\Model\Object`.
With that you can easly check the restriction of your object:

```php
<?php

$list = new Object\YourObject\Listing();
$objects = $list->getObjects();

foreach($objects as $object)
{
    echo $object->getRestricted(); //bool true|false
}

?>
```
