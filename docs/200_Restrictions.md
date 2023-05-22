# Restrictions
The Restriction Feature is disabled by default.

```yaml
members:
    restriction:
        enabled: true
```

> Note: You need to create some groups before you can enable the restrictions.

## Enable Document Restriction
Once activated, you'll see a restriction tab in every document.

### Document Inheritance
If your enable the inheritance checkbox, all child objects will inherit the restriction.

> If you're adding a new child element to an inheritable document, it will automatically adopt the restriction.

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
If your enable the inheritance checkbox, all child objects will inherit the restriction.

> If you're adding a new child element to an inheritable object, it will automatically adopt the restriction.

## Asset Restriction
After you've activated the restriction globally, you're able to restrict assets.

**Important:** Only Assets within the `/restricted-assets` folder are able to be restricted!

### Assets Inheritance
Since assets can't have child assets you need to create a folder first.
Open the folder, and you'll see the inheritance checkbox. If you activate it, all assets will inherit all the restriction information from this folder.

> If you're adding a new asset into an inheritable folder, it will automatically adopt the restriction.

### Public Assets Path Protection
Out of the box, Members **can't protect** asset thumbnails of all kinds which are located inside the `/restricted-assets` folder. 
This can be an issue if you want to show document thumbnails or video thumbnails directly on the webpage.

On the other hand, it's also not possible to stream raw video assets in frontend (since the folder itself is protected by htaccess rules).

If you want to ensure 100% safe asset processing, you may want to enable the public asset path protection.
This feature is disabled by default and requires modifications of your global `.htaccess` file if you want to use it.

```yaml
members:
    restriction:
        enabled: true
        enable_public_asset_path_protection: true
```

```apacheconf
# add this at the top in public/.htaccess
RewriteEngine On
RewriteCond %{HTTP_HOST}==%{HTTP_REFERER} !^(.*?)==https?://\1/admin/ [OR]
RewriteCond %{HTTP_COOKIE} !^.*pimcore_admin_sid.*$ [NC]
RewriteRule ^restricted-assets/.* - [F,L]
RewriteRule ^var/.*/restricted-assets(.*) - [F,L]
RewriteRule ^cache-buster\-[\d]+/restricted-assets(.*) - [F,L]
```

## Global Restriction Check Helper
To get current restriction information about a document, object or asset, just call the restriction manager:

```php
<?php

use MembersBundle\Manager\RestrictionManager;

$element = Pimcore\Model\Asset::getById(1);
/** @var \MembersBundle\Restriction\ElementRestriction $restriction */
$restriction = $this->container->get(RestrictionManager::class)->getElementRestrictionStatus($element);

//get restriction group ids
echo $restriction->getRestrictionGroups();

//get section
echo $restriction->getSection();

//get state
echo $restriction->getState();
```