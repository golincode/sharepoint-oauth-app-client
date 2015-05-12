# SharePoint Folder
The `SPFolder` class handles all the folder operations in SharePoint.

## Get by GUID
Gets a SharePoint Folder by it's GUID

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token
    $site->createSPAccessToken();

    $folder = SPFolder::getByGUID($site, '00000000-0000-ffff-0000-000000000000');

} catch (SPException $e) {
    // handle exceptions
}
```

## Get by relative URL
Gets a SharePoint Folder by it's relative URL

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token
    $site->createSPAccessToken();

    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');

} catch (SPException $e) {
    // handle exceptions
}
```

## Get subfolders
Gets all the Folders within a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token
    $site->createSPAccessToken();

    $folders = SPFolder::getSubFolders($site, 'myFolder');
    
    // do something with the folders
    foreach ($folders as $folder) {
        var_dump($folder);
    }

} catch (SPException $e) {
    // handle exceptions
}
```

## Create
Create a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPList;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPFormDigest();

    // get a Folder
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');

    // get a List
    $folder = SPList::getByTitle($site, 'My List');

    $name = 'mySubfolder';

    $newFolder = SPFolder::create($folder, $name);

} catch (SPException $e) {
    // handle exceptions
}
```

A SharePoint Folder can be created inside a Folder or a List.

## Update
Update a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPFormDigest();

    // get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    $properties = [
        'Name' => 'Foo',
    ];

    $folder = $folder->update($properties);

} catch (SPException $e) {
    // handle exceptions
}
```

## Delete
Delete a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFolder;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPFormDigest();

    // get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    $folder->delete();

} catch (SPException $e) {
    // handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPFolder` object.

```php
    var_dump($folder->toArray());
    
    // array(11) {
    //   ["type"]=>
    //   string(9) "SP.Folder"
    //   ["guid"]=>
    //   string(36) "00000000-0000-ffff-0000-000000000000"
    //   ["title"]=>
    //   string(8) "myFolder"
    //   ["name"]=>
    //   string(8) "myFolder"
    //   ["created"]=>
    //   object(Carbon\Carbon)#55 (3) {
    //   ["date"]=>
    //     string(26) "2000-01-01 00:00:00.000000"
    //     ["timezone_type"]=>
    //     int(3)
    //     ["timezone"]=>
    //     string(13) "Europe/London"
    //   }
    //   ["modified"]=>
    //   object(Carbon\Carbon)#59 (3) {
    //     ["date"]=>
    //     string(26) "2000-01-01 00:00:00.000000"
    //     ["timezone_type"]=>
    //     int(3)
    //     ["timezone"]=>
    //     string(13) "Europe/London"
    //   }
    //   ["relative_url"]=>
    //   string(31) "/sites/mySite/myFolder"
    //   ["items"]=>
    //   array(0) {
    //   }
    //   ["item_count"]=>
    //   int(1)
    //   ["extra"]=>
    //   array(0) {
    //   }
    // }
```

## Properties
`SPFolder` property methods belong to a trait and are documented [here](SPProperties.md).

## Timestamps
`SPFolder` timestamp methods belong to a trait and are documented [here](SPTimestamps.md).