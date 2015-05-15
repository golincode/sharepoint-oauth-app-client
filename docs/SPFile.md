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

    // get a List by title
    $folder = SPList::getByTitle($site, 'My List');

    // get all the Files from the Folder/List we just got
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

    // get a List by title
    $folder = SPList::getByTitle($site, 'My List');

    // content from an SplFileInfo object
    $content = new SplFileInfo('document.pdf');
    $name = null; // if null, the file name from the SplFileInfo will be used

    // content from a resource
    $content = fopen('document.pdf', 'r');
    $name = 'document.pdf'; // an SPException will be thrown if the name is not provided

    // content from a string
    $content = 'Document content...';
    $name = 'document.pdf'; // an SPException will be thrown if the name is not provided

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

    // get a List by title
    $folder = SPList::getByTitle($site, 'My List');

    $file = SPFile::getByName($folder, 'document.pdf');

    // content from an SplFileInfo object
    $content = new SplFileInfo('document2.pdf');

    // content from a resource
    $content = fopen('document2.pdf', 'r');

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

    // get a List by title
    $folder1 = SPList::getByTitle($site, 'My List');

    // get another List by title
    $folder2 = SPList::getByTitle($site, 'My Other List');

    // get the File we want to move
    $file = SPFile::getByName($folder1, 'document.pdf');

    // rename the file
    $name = 'moved_document.pdf'; // if null, the original name will be used

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

    // get a List by title
    $folder1 = SPList::getByTitle($site, 'My List');

    // get another List by title
    $folder2 = SPList::getByTitle($site, 'My Other List');

    // get the File we want to copy
    $file = SPFile::getByName($folder1, 'document.pdf');

    // rename the file
    $name = 'copied_document.pdf'; // if null, the original name will be used
    
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

    // get a List by title
    $folder = SPList::getByTitle($site, 'My List');

    // get the File we want to delete
    $file = SPFile::getByName($folder, 'document.pdf');

    $file->delete();

} catch (SPException $e) {
    // handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPFile` object.

```php
    var_dump($file->toArray());
    
    // array(11) {
    //   ["type"]=>
    //   string(18) "SP.Data.mySubfolderItem"
    //   ["id"]=>
    //   int(123)
    //   ["guid"]=>
    //   string(36) "00000000-0000-ffff-0000-000000000000"
    //   ["title"]=>
    //   NULL
    //   ["name"]=>
    //   string(12) "document.pdf"
    //   ["size"]=>
    //   string(5) "65536"
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
    //   string(31) "/sites/mySite/myFolder/mySubfolder/document.pdf"
    //   ["author"]=>
    //   string(55) "i:0i.t|membership|username@example.onmicrosoft.com"
    //   ["extra"]=>
    //   array(0) {
    //   }
    // }
```

## Get ID
Get the `SPFile` id.

```php
echo $file->getID(); // 123
```

## Get name
Get the `SPFile` name.

```php
echo $file->getName(); // document.pdf
```

## Get size
Get the `SPFile` size.

```php
echo $file->getSize(); // 65536
```

## Get relative URL
Get the `SPFile` relative URL.

```php
echo $file->getRelativeUrl(); // /sites/mySite/myFolder/mySubfolder/document.pdf
```

## Get URL
Get the `SPFile` URL.

```php
echo $file->getUrl(); // https://example.sharepoint.com/sites/mySite/myFolder/mySubfolder/document.pdf
```

## Get author
Get the `SPFile` URL.

```php
echo $file->getAuthor(); // i:0i.t|membership|username@example.onmicrosoft.com
```

## Get contents
Get the contents of the `SPFile`.

```php
file_put_contents('document.pdf', $file->getContents());
```

## Get metadata
This method is similar to the `toArray()` one, with the exception that it includes the `url` and excludes the `type`, `title`, `relative_url`, `author` and `extra`.

```php
    var_dump($file->toArray());
    
    // array(11) {
    //   ["id"]=>
    //   int(123)
    //   ["guid"]=>
    //   string(36) "00000000-0000-ffff-0000-000000000000"
    //   ["name"]=>
    //   string(12) "document.pdf"
    //   ["size"]=>
    //   string(5) "65536"
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
    //   ["url"]=>
    //   string(78) "https://example.sharepoint.com/sites/mySite/myFolder/mySubfolder/document.pdf"
    // }
```

## Get SharePoint Item
Get the associated SharePoint Item of a `SPFile`. This method is normally used when the metadata of a `SPFile` needs to be set.

```php
try {
    $item = $file->getSPItem();
    
    $item->update([
        'Title'        => 'A PDF Document',
        
        // custom fields
        'CustomField1' => 'Foo',
    ]);
} catch (SPException $e) {
    // handle exceptions
}
```

## Properties
`SPFile` property methods belong to a trait and are documented [here](SPProperties.md).

## Timestamps
`SPFile` timestamp methods belong to a trait and are documented [here](SPTimestamps.md).