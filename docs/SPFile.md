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
	$folder = SPFolder::getByRelativeURL($site, 'myFolder');
	
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

	$file = SPFile::getByRelativeURL($site, 'myFolder/mySubfolder/image.png');

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
	$folder = SPFolder::getByRelativeURL($site, 'myFolder/mySubfolder');

	$file = SPFile::getByName($folder, 'image.png');

} catch (SPException $e) {
    // handle exceptions
}
```