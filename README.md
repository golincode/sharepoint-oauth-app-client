# SharePoint OAuth App Client

The **SharePoint OAuth App Client** is a set of [PHP](http://www.php.net) classes that allows you to authenticate with SharePoint Online (2013) via [OAuth2](http://oauth.net/2/) and work with a subset of SharePoint's functionality (currently **Lists**, **Items** and **Users**).

This library is available to anyone and is licensed under the MIT license.

## Installation

### Requirements
* PHP 5.4+
* [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [PHP-JWT](https://packagist.org/packages/nixilla/php-jwt)
* [Carbon](https://packagist.org/packages/nesbot/carbon)


#### Using Composer

Add [wearearchitect/sharepoint-oauth-app-client](https://packagist.org/packages/wearearchitect/sharepoint-oauth-app-client) to your `composer.json` and run **composer install** or **composer update**.

	{
		"require": {
			"wearearchitect/sharepoint-oauth-app-client": "0.9.*"
		}
	}


## Basic usage

### Class instantiation

	<?php

	require 'vendor/autoload.php';

	use WeAreArchitect\SharePoint\SPException;
	use WeAreArchitect\SharePoint\SPSite;

	try {
			$config = [
				'url'       => 'https://example.sharepoint.com/sites/mySite',
				'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
				'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
				'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
			];

		$site = new SPSite($config);

	} catch(SPException $e) {
		// handle exceptions
	}

**Attention:** In order to use the methods provided by this class, you need an **Access Token** which can be requested through a logged **User** or an **App only policy**.


### Get an Access Token as a **User**

	try {
		/**
		 * Access Token request config requirements as a User:
		 *
		 * - SharePoint Context Token
		 * - Secret
		 */
		$context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

		/**
		 * Using the method provided by the SPSite class
		 * which will store the Access Token internally
		 */
		$site->createAccessTokenFromUser($context_token);

		/**
		 * Using the static method from the SPAccessToken class
		 * which will require manually setting the Access Token
		 * in the SPSite object
		 */
		$token = SPAccessToken::createFromUser($site, $context_token);

		$site->setAccessToken($token);

	} catch(SPException $e) {
		// handle exceptions
	}

**Note:** The context token is accessible from the **SPAppToken** input (via POST) when SharePoint redirects to the Application.


### Get an Access Token as an **AOP (App only policy)**

	try {
		/**
		 * Access Token request requirements as an App only policy:
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
		$site->createAccessTokenFromAOP();

		/**
		 * Using the static method from the SPAccessToken class
		 * which will require manually setting the Access Token
		 * in the SPSite object
		 */
		$token = SPAccessToken::createFromAOP($site);

		$site->setAccessToken($token);

	} catch(SPException $e) {
		// handle exceptions
	}

# More documentation soon #
