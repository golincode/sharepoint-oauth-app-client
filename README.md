# SharePoint OAuth App Client
The **SharePoint OAuth App Client** is a [PHP](http://www.php.net) library that makes it easy to authenticate via [OAuth2](http://oauth.net/2/) with the SharePoint Online (2013) REST API and work with **Lists**, **Folders**, **Items**, **Files** and **Users**.

This library aims for [PSR-1][], [PSR-2][] and [PSR-4][] standards compliance.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Requirements
* [PHP](http://www.php.net) 5.4+
* [Guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [PHP-JWT](https://packagist.org/packages/firebase/php-jwt)
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
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
        ]
    ];

    // create a SharePoint Site instance
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // generate an Access Token (App-only Policy)
    $site->createSPAccessToken();

    // get all the Lists and respective Items 
    $lists = SPList::getAll($site, [
        'fetch' => true,
    ]);

    // iterate through each List
    foreach ($lists as $list) {
        var_dump($list);

        // iterate through each List Item
        foreach ($list as $item) {
            var_dump($item);
        }
    }

} catch (SPException $e) {
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

## SharePoint credentials
See how to generate the needed credentials [here](Credentials.md). 

## Troubleshooting
Check for common library issues and how to [solve them](docs/Troubleshooting.md)

## SharePoint Documentation
- [Working with lists and list items with REST](https://msdn.microsoft.com/en-us/library/office/dn292552%28v=office.15%29.aspx)
- [Working with folders and files with REST](https://msdn.microsoft.com/en-us/library/office/dn292553%28v=office.15%29.aspx)
- [Files and folders REST API reference](https://msdn.microsoft.com/en-us/library/office/dn450841%28v=office.15%29.aspx)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
