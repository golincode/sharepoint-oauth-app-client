# SharePoint OAuth App Client
The **SharePoint OAuth App Client** is a [PHP](http://www.php.net) library that makes it easy to authenticate via [OAuth2](http://oauth.net/2/) with the SharePoint Online (2013) REST API and use **Lists**, **Folders**, **Items**, **Files** and **Users**.
 
## Requirements
* [PHP](http://www.php.net) 5.4+
* [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [PHP-JWT](https://packagist.org/packages/nixilla/php-jwt)
* [Carbon](https://packagist.org/packages/nesbot/carbon)

## Installation
``` bash
composer require "wearearchitect/sharepoint-oauth-app-client:0.9.*"
```

## Basic usage example
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPList;
use WeAreArchitect\SharePoint\SPSite;

try {
	$settings = [
		'site' => [
			'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
		]
	];

	// create a SharePoint Site instance
	$site = SPSite::create('https://example.sharepoint.com/sites/mySite', $settings);

	// generate an Access Token through App Only Policy
	$site->createSPAccessTokenFromAOP();

	// get all the Lists and respective Items 
	$lists = SPList::getAll($site, [
		'fetch' => true
	]);

	// iterate through each List
	foreach ($lists as $list) {
		var_dump($list);

		// iterate through each List Item
		foreach ($list as $item) {
			var_dump($item);
		}
	}

} catch(SPException $e) {
	// handle exceptions
}
```

## Class documentation
- [SPSite](docs/SPSite.md)
- [SPAccessToken](docs/SPAccessToken.md)
- [SPFormDigest](docs/SPFormDigest.md)
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)
- [SPUser](docs/SPUser.md)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
