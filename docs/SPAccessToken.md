# SharePoint Access Token
In order to work with SharePoint **Lists**, **Folders**, **Items**, **Files** or **Users**, an Access Token is needed.

Access Tokens can have two authorization policies: **App-only Policy** and **User-only Policy**

## Instantiation (App-only Policy)
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

} catch (SPException $e) {
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

} catch (SPException $e) {
	// handle exceptions
}
```

## Instantiation (User-only Policy)
Like with the App-only Policy **SPAccessToken**, there's also two ways to instantiate a User-only Policy **SPAccessToken**.

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

	$context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

	$site->createSPAccessToken($context_token);

	$token = $site->getSPAccessToken();

} catch (SPException $e) {
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

	$context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

	$token = SPAccessToken::createUOP($site, $context_token);

} catch (SPException $e) {
	// handle exceptions
}
```

**Note:** On both App-only Policy examples, the context token comes from the **SPAppToken** HTTP POST field when the SharePoint application launches.