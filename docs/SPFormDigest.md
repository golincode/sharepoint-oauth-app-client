# SharePoint Form Digest
To modify a SharePoint **List**, **Folder**, **Item** or **File**, a Form Digest needs to be set beforehand.

## Instantiation
There are two ways to instantiate a `SPFormDigest` object.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPFormDigest;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $site->createSPFormDigest();

    $digest = $site->getSPFormDigest();

} catch (SPException $e) {
    // handle exceptions
}
```

### via class factory
```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPFormDigest;
use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $digest = SPFormDigest::create($site);

    $site->setSPFormDigest($digest);

} catch (SPException $e) {
    // handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPFormDigest` object.

```php
    var_dump($digest->toArray());
    
    // array(3) {
    //     ["digest"]=>
    //     string(157) "0x79EAB4CE687BD3DE6B9A87177CC6430759744CDED8C2605..."
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
Check if the `SPFormDigest` has expired.

```php
    if ($digest->hasExpired()) {
        // it's time to get a new digest
    } else {
        // looking good
    }
```

## Expire date
Get the expiration date of a `SPFormDigest` in the form of a `Carbon` object.

```php
$carbon = $digest->expireDate();

echo $carbon->diffForHumans(); // 29 minutes from now
```

## To String
The `SPFormDigest` class implements the `__toString` magic method, which enables us to get the token value when we treat the object as a `string`. 

```php
echo $digest; // 0x79EAB4CE687BD3DE6B9A87177CC6430759744CDED8C2605...
```

## Serialization
The `SPFormDigest` class implements the `Serializable` interface.
This allows saving the digest to use at a later time, avoiding new digest requests to the SharePoint API each time something needs doing.

```php
    // serialize the digest
    $serialized = serialize($digest);
    
    // store it in a database
    
    // when needed, get it back

    // unserialize the data
    $oldDigest = unserialize($serialized);
    
    // check if it's still valid
    if ($oldDigest->hasExpired()) {
        // request a new digest from the API
    }

    // do something
```
