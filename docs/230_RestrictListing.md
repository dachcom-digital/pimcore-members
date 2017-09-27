# Restricted Listing
Unfortunately it's impossible to inject some global sql additions into document/asset/object listing queries. 
Because of that, you need to take care about that by yourself. There are two ways to check a element restriction

## 1. Listing Query Injection 
This is the recommended way.

```php
<?php

use Pimcore\Model\DataObject;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use MembersBundle\Security\RestrictionQuery;

$listing = DataObject\YourObject::getList();
$listing->onCreateQuery(function (QueryBuilder $query) use ($listing) {
    $this->container->get(RestrictionQuery::class)
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
/** @var \MembersBundle\Restriction\ElementRestriction $restriction */
$restriction = $this->container->get(RestrictionManager::class)->getElementRestrictionStatus($element);
if($restriction->getSection() === RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
    //allowed!
}
```