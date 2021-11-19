# Restricted Listing
Unfortunately it's impossible to inject some global sql additions into document/asset/object listing queries. 
Because of that, you need to take care about that by yourself. There are two ways to check a element restriction

## 1. Listing Query Injection 
This is the recommended way.

```php
<?php

use Pimcore\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use MembersBundle\Security\RestrictionQuery;

public function defaultAction(RestrictionQuery $restrictionQuery)
{
    # Data Objects
    $listing = Model\DataObject\YourObject::getList();
    $listing->onCreateQueryBuilder(function (QueryBuilder $query) use ($restrictionQuery, $listing) {
        $restrictionQuery->addRestrictionInjection($query, $listing);
    });

    dump($listing->getObjects());

    # Documents
    $listing = new Model\Document\Listing();
    $listing->setLimit(10);
    $listing->onCreateQueryBuilder(function (QueryBuilder $query) use ($listing, $restrictionQuery) {
       $restrictionQuery->addRestrictionInjection($query, $listing, 'id');
    });

    dump($listing->getDocuments());

    # Assets
    $listing = new Model\Asset\Listing();
    $listing->setLimit(10);
    $listing->onCreateQueryBuilder(function (QueryBuilder $query) use ($listing, $restrictionQuery) {
       $restrictionQuery->addRestrictionInjection($query, $listing, 'id');
    });

    dump($listing->getAssets());
}
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