# Restricted Listing
Unfortunately it's impossible to inject some global sql additions into document/asset/object listing queries. 
Because of that, you need to take care about that by yourself. There are two ways to check a element restriction

## 1. Listing Query Injection 
This is the recommended way.

```php
<?php

use Pimcore\Model\DataObject;
use Pimcore\Db\ZendCompatibility\QueryBuilder;

$listing = DataObject\YourObject::getList();
$listing->onCreateQuery(function (QueryBuilder $query) use ($listing) {
    $this->container->get('members.security.restriction.query')
        ->addRestrictionInjection($query, $listing);
});

$availableObjects = $listing->getObjects();
```

## 2. Statements 
If you can't or won't modify the sql query, you can use these statements to check the restriction:

```php
<?php

use MembersBundle\Manager\RestrictionManager;

$element = 'your_object|your_asset|your_document';
$assetRestriction = $this->container->get('members.manager.restriction')->getElementRestrictionStatus($element);
if($assetRestriction['section'] === RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
    //allowed!
}
```