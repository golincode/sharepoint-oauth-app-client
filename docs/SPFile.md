# SharePoint File
The `SPFile` class handles all the file operations in SharePoint.

## Get all
Gets all the SharePoint Files from a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    // get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');
    
    // get all the Files from the Folder we just got
    $files = SPFile::getAll($folder);
    
    // do something with the files
    foreach ($files as $file) {
        var_dump($file);
    }

} catch (SPException $e) {
    // handle exceptions
}
```

## Get by relative URL
Gets a SharePoint File by it's relative URL

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    $file = SPFile::getByRelativeUrl($site, 'myFolder/mySubfolder/image.png');

} catch (SPException $e) {
    // handle exceptions
}
```

## Get by name
Gets a SharePoint File by it's name

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    // get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    $file = SPFile::getByName($folder, 'image.png');

} catch (SPException $e) {
    // handle exceptions
}
```

## Create
Create a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    // content from an SplFileInfo object
    $content = new SplFileInfo('document.doc');
    $name = null; // if null, the file name from the SplFileInfo will be used

    // content from a resource
    $content = fopen('document.doc', 'r');
    $name = 'document.doc'; // an SPException will be thrown if the name is not provided

    // content from a string
    $content = 'Document content...';
    $name = 'document.doc'; // an SPException will be thrown if the name is not provided

    // allow overwriting the file if it already exists
    $overwrite = false; // an SPException will be thrown if the file exists and we didn't allow overwriting

    $file = SPFile::create($folder, $content, $name, $overwrite);

} catch (SPException $e) {
    // handle exceptions
}
```

## Update
Update a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    $file = SPFile::getByName($folder, 'document.doc');

    // content from an SplFileInfo object
    $content = new SplFileInfo('document2.doc');

    // content from a resource
    $content = fopen('document2.doc', 'r');

    // content from a string
    $content = 'New document content...';

    $file = $file->update($content);

} catch (SPException $e) {
    // handle exceptions
}
```

## Move/rename
Move and/or rename a SharePoint File.

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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
    $folder1 = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // get another Folder by relative URL
    $folder2 = SPFolder::getByRelativeUrl($site, 'otherFolder');

    // get the File we want to move
    $file = SPFile::getByName($folder1, 'document.doc');

    // rename the file
    $name = 'moved_document.doc'; // if null, the original name will be used

    $file->move($folder2, $name);

} catch (SPException $e) {
    // handle exceptions
}
```

## Copy
Copy a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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
    $folder1 = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // get another Folder by relative URL
    $folder2 = SPFolder::getByRelativeUrl($site, 'otherFolder');

    // get the File we want to copy
    $file = SPFile::getByName($folder1, 'document.doc');

    // rename the file
    $name = 'copied_document.doc'; // if null, the original name will be used
    
    // allow overwriting the file if it already exists
    $overwrite = false; // an SPException will be thrown if the file exists and we didn't allow overwriting

    $file->copy($folder2, $name, $overwrite);

} catch (SPException $e) {
    // handle exceptions
}
```

## Delete
Delete a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPFile;
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

    // get the File we want to delete
    $file = SPFile::getByName($folder, 'document.doc');

    $file->delete();

} catch (SPException $e) {
    // handle exceptions
}
```
