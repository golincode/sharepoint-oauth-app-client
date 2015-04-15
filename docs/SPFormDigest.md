# SharePoint Form Digest
To modify a SharePoint **List**, **Folder**, **Item** or **File**, a Form Digest needs to be set beforehand.

## Instantiation
There are two ways to instantiate a `SPFormDigest` object.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPFormDigest;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $site->createSPFormDigest();

    $digest = $site->getSPFormDigest();

} catch (SPException $e) {
    // handle exceptions
}
```

### via class factory
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPFormDigest;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $digest = SPFormDigest::create($site);

    $site->setSPFormDigest($digest);

} catch (SPException $e) {
    // handle exceptions
}
```
