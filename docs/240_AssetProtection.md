# Asset Protection

Generate protected asset download urls:

```php
<?php

//this asset needs to be a protected one!
$download = \Pimcore\Model\Asset::getById(1);
$downloadLink = $this
                    ->container->get('members.security.restriction.uri')
                    ->generateAssetUrl($download);

//$downloadLink: domain.com/members/request-data/W3siZiI6NiwicCI6ZmFsc2V9XQ

```

If you want to get multiple downloads at once, you can use the generateAssetPackageUrl() method. 
This will create a zip file on the fly, so no temp files on your server!

```php
<?php


//these assets needs to be protected!
$download1 = \Pimcore\Model\Asset::getById(1);
$download2 = \Pimcore\Model\Asset::getById(2);

$packageData = [
    ['asset' => $download1],
    ['asset' => $download2]
];

$downloadLink = $this
                    ->container->get('members.security.restriction.uri')
                    ->generateAssetPackageUrl($packageData);

//$downloadLink: domain.com/members/request-data/W3siZiI6NiwicCI6ZmFsc2V9XQ

```
