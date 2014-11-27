# SharePoint Site
The **SPSite** class is the foundation for all the other classes of the **SharePoint OAuth App Client** library.
It handles HTTP requests and manages [Access Tokens](SPAccessToken.md) and [Form Digests](SPFormDigest.md).

## Instantiation
There are two ways to create an **SPSite** instance.

### via constructor
```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
	$settings = [
		'site' => [
			'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
		]
	];

	$http = new Client([
		'base_url' => 'https://example.sharepoint.com/sites/mySite/'
	]);
	
	$site = new SPSite($http, $settings);

} catch(SPException $e) {
	// handle exceptions
}
```

### via create() method
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
	$settings = [
		'site' => [
			'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
			'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
		]
	];

	$site = SPSite::create('https://example.sharepoint.com/sites/mySite', $settings);

} catch(SPException $e) {
	// handle exceptions
}
```
