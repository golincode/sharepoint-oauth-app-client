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

### via factory
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

	$site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

} catch(SPException $e) {
	// handle exceptions
}
```

## Configuration
Retrieve the **SPSite** configuration array.

```php
	$config = $site->getConfig();

	var_dump($config);

	// [
	//     'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
	//     'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
	//     'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
	// ];
```

## Hostname
Retrieve the **SPSite** hostname.

```php
	echo $site->getHostname(); // https://example.sharepoint.com

	echo $site->getHostname('/sites/mySite'); // https://example.sharepoint.com/sites/mySite
```

## Path
Retrieve the **SPSite** path.

```php
	echo $site->getPath(); // /sites/mySite/

	echo $site->getPath('/stuff'); // /sites/mySite/stuff
```

## URL
Retrieve the **SPSite** URL.

```php
	echo $site->getURL(); // https://example.sharepoint.com/sites/mySite

	echo $site->getURL('/stuff'); // https://example.sharepoint.com/sites/mySite/stuff
```

## Logout URL
Retrieve the **SPSite** logout URL.

```php
	echo $site->getLogoutURL(); // https://example.sharepoint.com/sites/mySite/_layouts/SignOut.aspx
```

## HTTP request
Make an HTTP request to the SharePoint API. Use this method when extending the class with new methods or for debugging purposes.

```php
	// [HTTP GET] get the most popular tags
	$json = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
		'headers' => [
			'Authorization' => 'Bearer '.$site->getSPAccessToken(),
			'Accept'        => 'application/json;odata=verbose'
		],
	]);

	// [HTTP POST] follow a user
	$json = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
		'headers' => [
			'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
			'Accept'          => 'application/json;odata=verbose',
			'X-RequestDigest' => (string) $site->getSPFormDigest()
		],
		'query' => [
			'@v' => 'i:0#.f|membership|user@example.onmicrosoft.com'
		]
	], 'POST');
```
The **$json** variable will contain an array with the API response on a successful request.
If any error occurs, an **SPException** will be thrown.

To **debug** an API response, the 4th argument should be set to **false**. So, to debug the above examples we would do:
```php
	// [HTTP GET] get the most popular tags
	$json = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
		'headers' => [
			'Authorization' => 'Bearer '.$site->getSPAccessToken(),
			'Accept'        => 'application/json;odata=verbose'
		],
	], 'GET', false);

	// [HTTP POST] follow a user
	$json = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
		'headers' => [
			'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
			'Accept'          => 'application/json;odata=verbose',
			'X-RequestDigest' => (string) $site->getSPFormDigest()
		],
		'query' => [
			'@v' => 'i:0#.f|membership|user@example.onmicrosoft.com'
		]
	], 'POST', false);
```
Instead of an **array**, a **GuzzleHttp\Message\Response** object will be returned.

For further information on the API endpoints used in the examples, see the [User profiles REST API reference](https://msdn.microsoft.com/EN-US/library/office/dn790354%28v=office.15%29.aspx#bk_PeopleManagerEndpoint).