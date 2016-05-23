# Pimcore Members

Just download and install it into your plugin folder.

#### Requirements
* Pimcore 4.0 (at least build 3784)

#### Features
* Create Members in Backend
* Allow Members to register in frontend
* Restrict Documents to specific User Roles

### Documents

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


### Assets

Pure Asset restriction is not supported right now. However, Members will install a protected folder
called "restricted-assets". all assets placed in this folder are protected from frontend calls.

**Get Asset Data**

To get the asset data you need to take some simple steps:
 
1. create an object class called "download"
3. set the object parent class (see section *"Objects"* below) 
3. add a file field called *"file"* and text field called  *"title"*
4. create a download object, add a asset to the file field
5. add restriction to object (*"access privileges"* tab)
6. call the UrlServant Helper, see example below.

*Example*

```html
<a href="<?= \Members\Tool\UrlServant::generateAssetUrl($download->getFile(), $download->getId()); ?>">
    <?= $download->getTitle(); ?>
</a>
```

If you want to get multiple downloads at once, you can use the `generateAssetPackageUrl()` method.
This will create a zip file on the fly, so no temp files on your server!

*Example*

```php
<?php

    $packageData = array(
        array('asset' => $download1->getFile(), 'objectProxyId' => $download1->getId()),
        array('asset' => $download2->getFile(), 'objectProxyId' => $download2->getId())
    );
    
?>

<a href="<?= \Members\Tool\UrlServant::generateAssetPackageUrl( $packageData ); ?>">Download Zip</a>

```


### Objects

Just extend your object classes with `\Members\Model\Object`. Now you're able to check the restriction of your object:

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