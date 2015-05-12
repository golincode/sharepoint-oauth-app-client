# SharePoint Timestamps
The `SPTimestampsTrait` contains methods related to common date/time properties of SharePoint objects.

## Classes using this trait
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)

## Creation time
Get the creation time as a `Carbon` object of a `SPList`, `SPItem`, `SPFolder` or `SPFile` object.
           
```php
    var_dump($object->getTimeCreated());
    
    // object(Carbon\Carbon)#55 (3) {
    //   ["date"]=>
    //   string(26) "2000-01-01 00:00:00.000000"
    //   ["timezone_type"]=>
    //   int(3)
    //   ["timezone"]=>
    //   string(13) "Europe/London"
    // }
```

## Modification time
Get the modification time as a `Carbon` object of a `SPList`, `SPItem`, `SPFolder` or `SPFile` object.
           
```php
    var_dump($object->getTimeModified());
    
    // object(Carbon\Carbon)#55 (3) {
    //   ["date"]=>
    //   string(26) "2000-01-01 00:00:00.000000"
    //   ["timezone_type"]=>
    //   int(3)
    //   ["timezone"]=>
    //   string(13) "Europe/London"
    // }
```
