# Restrictions

The Restriction Feature is disable by default. So activate it first:

```yaml
members:
    restriction:
        enabled: true
```

> Note: You need to create some groups, before you can enable the restrictions.

## Enable Document Restriction
Once activated, you'll see a restriction tab in every document.

### Document Inheritance
If your enable the inheritance checkbox, all subdocuments will inherit the restriction.

> If you're adding a new child element to a inheritable document, it will automatically adopt the restriction.

## Enable Object Restriction
If you want to restrict object, you need to define them in the members configuration first:

```yaml
members:
    restriction:
        enabled: true
        allowed_objects:
            - 'NewsEntry'
            - 'YourObjectName'
```
Now you should see a restriction tab in all of those defined objects.

### Object Inheritance
If your enable the inheritance checkbox, all subobjects will inherit the restriction.

> If you're adding a new child element to a inheritable object, it will automatically adopt the restriction.

## Asset Restriction
After you've activated the restriction globally, you're able to restrict assets.

**Important:** Only Assets within the `/restricted-assets` folder are able to restrict!

### Assets Inheritance
Since assets can't have child assets you need to create a folder first.
Open the folder and you'll see the inheritance checkbox. If you activate it, all assets will inherit all the restriction information from this folder.

> If you're adding a new asset into a inheritable folder, it will automatically adopt the restriction.

## Global Restriction Check Helper
To get current restriction information about a document, object or asset, just call the restriction manager:

```php
<?php

$element = Pimcore\Model\Asset::getById(1);
/** @var \MembersBundle\Restriction\ElementRestriction $restriction */
$restriction = $this->container->get('members.manager.restriction')->getElementRestrictionStatus($element);

//get restriction group ids
echo $restriction->getRestrictionGroups();

//get section
echo $restriction->getSection();

//get state
echo $restriction->getState();

```