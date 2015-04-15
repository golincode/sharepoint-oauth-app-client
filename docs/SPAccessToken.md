# SharePoint Access Token
In order to work with SharePoint **Lists**, **Folders**, **Items**, **Files** or **Users**, an Access Token is needed.

Access Tokens can have two authorization policies: **App-only Policy** and **User-only Policy**

## Instantiation (App-only Policy)
There are two ways to create a new **App-only Policy** `SPAccessToken` instance.

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

### via class factory
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

    $site->setSPAccessToken($token);

} catch (SPException $e) {
    // handle exceptions
}
```

## Instantiation (User-only Policy)
Like with the **App-only Policy** `SPAccessToken`, there's also two ways to instantiate a **User-only Policy** `SPAccessToken`.

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

### via class factory
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

    $site->setSPAccessToken($token);

} catch (SPException $e) {
    // handle exceptions
}
```

**Note:** On both **User-only Policy** examples above, the context token comes from the `SPAppToken` HTTP POST field when the SharePoint application launches.

## To array
Retrieve an `array` representation of the `SPAccessToken` object.

```php
    var_dump($token->toArray());
    
    // array(3) {
    //     ["token"]=>
    //     string(1132) "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik1uQ19WWmNBVG..."
    //     ["expires"]=>
    //     object(Carbon\Carbon)#28 (3) {
    //         ["date"]=>
    //         string(26) "2000-01-01 00:00:00.000000"
    //         ["timezone_type"]=>
    //         int(3)
    //         ["timezone"]=>
    //         string(13) "Europe/London"
    //     }
    //     ["extra"]=>
    //     array(0) {
    //     }
    // }
```

## Has expired
Check if the `SPAccessToken` has expired.

```php
    if ($token->hasExpired()) {
        // it's time to get a fresh token
    } else {
        // we're good
    }
```

## Expire date
Get the expiration date of a `SPAccessToken` in the form of a `Carbon` object.

```php
    $carbon = $token->expireDate();

    echo $carbon->diffForHumans(); // 12 hours from now
```

## To String
The `SPAccessToken` class implements the `__toString` magic method, which enables us to get the token value when we treat the object as a `string`. 

```php
    echo $token; // eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik1uQ19WWmNBVG...
```
