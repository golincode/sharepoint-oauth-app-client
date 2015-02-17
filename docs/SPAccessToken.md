# SharePoint Access Token
In order to work with SharePoint **Lists**, **Folders**, **Items**, **Files** or **Users**, an Access Token is needed.

Access Tokens can have two authorization policies: **App-only Policy** or **User-only Policy**.

## App-only Policy Instantiation
There are two ways to create a new App-only Policy **SPAccessToken** instance.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPAccessToken;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
	// SharePoint Site settings
	$settings = [
		// ...
	];

	$site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

	$site->createSPAccessToken();

	$token = $site->getSPAccessToken();

} catch(SPException $e) {
	// handle exceptions
}
```

### via factory
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPAccessToken;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
	// SharePoint Site settings
	$settings = [
		// ...
	];

	$site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

	$token = SPAccessToken::createAOP($site);

} catch(SPException $e) {
	// handle exceptions
}
```
