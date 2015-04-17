# SharePoint User
The `SPUser` class is an object representation of a SharePoint user.

## Get current user
Get a `SPUser` instance of the current logged user.

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;
use WeAreArchitect\SharePoint\SPUser;

try {
    // instantiate a SPSite class

    $user = SPUser::getCurrent($site);

} catch (SPException $e) {
    // handle exceptions
}
```

## Get by user account
Get a `SPUser` instance from a specific user account.

```php
<?php

require 'vendor/autoload.php';

use WeAreArchitect\SharePoint\SPException;
use WeAreArchitect\SharePoint\SPSite;
use WeAreArchitect\SharePoint\SPUser;

try {
    // instantiate a SPSite class

    $user = SPUser::getByAccount($site, 'i:0#.f|membership|username@example.onmicrosoft.com');

} catch (SPException $e) {
    // handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPUser` object.

```php
    var_dump($user->toArray());

    // App-only Policy
    // array(9) {
    //     ["account"]=>
    //     string(58) "i:0i.t|00000000-0000-ffff-0000-000000000000|app@sharepoint"
    //     ["email"]=>
    //     NULL
    //     ["full_name"]=>
    //     string(14) "app@sharepoint"
    //     ["first_name"]=>
    //     string(0) ""
    //     ["last_name"]=>
    //     string(0) ""
    //     ["title"]=>
    //     NULL
    //     ["picture"]=>
    //     NULL
    //     ["url"]=>
    //     string(132) "https://example.sharepoint.com/Person.aspx?accountname=i%3A0i%2Et%7C00000000%2D0000%2Dffff%2D0000%2D000000000000%7Capp%40sharepoint"
    //     ["extra"]=>
    //     array(0) {
    //     }
    // }

    // User-only Policy
    // array(9) {
    //     ["account"]=>
    //     string(58) "i:0i.t|membership|username@example.onmicrosoft.com"
    //     ["email"]=>
    //     string(33) "username@example.onmicrosoft.com"
    //     ["full_name"]=>
    //     string(12) "Name Surname"
    //     ["first_name"]=>
    //     string(4) "Name"
    //     ["last_name"]=>
    //     string(7) "Surname"
    //     ["title"]=>
    //     NULL
    //     ["picture"]=>
    //     NULL
    //     ["url"]=>
    //     string(74) "https://example.sharepoint.com/personal/username_example_onmicrosoft_com/"
    //     ["extra"]=>
    //     array(0) {
    //     }
    // }
```

## Account
Get the `SPUser` account.

```php
echo $user->getAccount(); // i:0i.t|membership|username@example.onmicrosoft.com
```

## Email
Get the `SPUser` email.

```php
echo $user->getEmail(); // username@example.onmicrosoft.com
```

## Full name
Get the `SPUser` full name.

```php
echo $user->getFullName(); // Name Surname
```

## First name
Get the `SPUser` first name.

```php
echo $user->getFirstName(); // Name
```

## Last name
Get the `SPUser` last name.

```php
echo $user->getLastName(); // Surname
```

## Title
Get the `SPUser` title.

```php
echo $user->getTitle(); // null
```

## Picture
Get the `SPUser` picture URL.

```php
echo $user->getPicture(); // null
```

## URL
Get the `SPUser` URL.

```php
echo $user->getURL(); // https://example.sharepoint.com/personal/username_example_onmicrosoft_com/
```
