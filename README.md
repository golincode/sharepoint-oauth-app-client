# SharePoint OAuth App Client

The **SharePoint OAuth App Client** is a [PHP](http://www.php.net) library that allows working with a subset of SharePoint Online (2013) functionality (currently **Lists**, **Folders**, **Items**, **Files** and **Users**) while authenticated via [OAuth2](http://oauth.net/2/).

### Library Requirements
* [PHP](http://www.php.net) 5.4+
* [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [PHP-JWT](https://packagist.org/packages/nixilla/php-jwt)
* [Carbon](https://packagist.org/packages/nesbot/carbon)


#### Installation

``` bash
$ composer require "wearearchitect/sharepoint-oauth-app-client:~0.9"
```

## SharePoint Site Class

### Traditional instantiation

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

### Static method instantiation

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

**Attention:** In order to use all the functionality this library provides, you need an **Access Token**.


### Get an Access Token as a **User**

```php
try {
	/**
	 * SharePoint Access Token request config requirements as a User:
	 *
	 * - SharePoint Context Token
	 * - Secret
	 */
	$context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

	/**
	 * Using the method provided by the SPSite class
	 * which will store the Access Token internally
	 */
	$site->createSPAccessTokenFromUser($context_token);

	/**
	 * Using the static method from the SPAccessToken class
	 * which will require manually setting the Access Token
	 * in the SPSite object
	 */
	$token = SPAccessToken::createFromUser($site, $context_token);

	$site->setSPAccessToken($token);

} catch(SPException $e) {
	// handle exceptions
}
```

**Note:** The context token is accessible from the **SPAppToken** input (via POST) when SharePoint redirects to the Application.


### Get an Access Token as an **AOP (App only policy)**

```php
try {
	/**
	 * SharePoint Access Token request requirements as an App only policy:
	 *
	 * - ACS
	 * - Client ID
	 * - Secret
	 * - Resource
	 */

	/**
	 * Using the method provided by the SPSite class
	 * which will store the Access Token internally
	 */
	$site->createSPAccessTokenFromAOP();

	/**
	 * Using the static method from the SPAccessToken class
	 * which will require manually setting the Access Token
	 * in the SPSite object
	 */
	$token = SPAccessToken::createFromAOP($site);

	$site->setSPAccessToken($token);

} catch(SPException $e) {
	// handle exceptions
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
