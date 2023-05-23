# Asset Protection

### Single Asset
Generate protected asset download urls:

```php
<?php

// add DI 
$this->restrictionUri = \MembersBundle\Security\RestrictionUri $restrictionUri;

//this asset needs to be a protected one!
$download = \Pimcore\Model\Asset::getById(1);
$downloadLink = $this->restrictionUri->generateAssetUrl($download);

//$downloadLink: domain.com/members/request-data/W3siZiI6NiwicCI6ZmFsc2V9XQ

```

### Package
If you want to get multiple downloads at once, you can use the generateAssetPackageUrl() method. 
This will create a temporary zip file containing all requested files!

>**Important:** Since we need to fetch all assets via stream by flysystem, it is no longer possible to push the zip file to the client on the fly.
> Therefor we need to store the archive in `PIMCORE_SYSTEM_TEMP_DIRECTORY` until the file has been downloaded. 
> 
> Depending on your system and archive size, this can temporarily consume a lot of space and memory! 

```php
<?php

// add DI 
$this->restrictionUri = \MembersBundle\Security\RestrictionUri $restrictionUri;

//these assets need to be protected!
$download1 = \Pimcore\Model\Asset::getById(1);
$download2 = \Pimcore\Model\Asset::getById(2);

$packageData = [
    ['asset' => $download1],
    ['asset' => $download2]
];

$downloadLink = $this->restrictionUri->generateAssetPackageUrl($packageData, true);

//since the second argument is set to TRUE, the link will only available if the current user is correctly authenticated.

//$downloadLink for users with same group restrictions: 'domain.com/members/request-data/W3siZiI6NiwicCI6ZmFsc2V9XQ'
//$downloadLink for guest users: ''
```

## Twig Extension
If you want to generate protected urls in twig, use these extensions to generate some:

### Single Asset
```twig
{# generate link #}
<a href="{{ members_generate_asset_url(12) }}" class="protected-link">download protected file</a>

{# generate protected link only if content is available for current user #}
{{ dump(members_generate_asset_url(12, true)) }}
```

### Package
```twig
{# generate link #}
<a href="{{ members_generate_asset_package_url([12,40]) }}" class="protected-link">download protected package</a>

{# generate protected link only if content is available for current user #}
{{ dump(members_generate_asset_package_url([12,40], true)) }}
```

> **Note:** Don't worry if you skip the second argument (default is false), since every download link will check for a valid user restriction!