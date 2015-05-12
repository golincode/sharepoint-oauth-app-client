# SharePoint Object
The `SPObject` class contains methods related to common attributes of SharePoint objects.

## Subclasses
- [SPAccessToken](docs/SPAccessToken.md)
- [SPFormDigest](docs/SPFormDigest.md)
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)
- [SPUser](docs/SPUser.md)

## Extra
Get an extra property of a `SPAccessToken`, `SPFormDigest`, `SPList`, `SPItem`, `SPFolder`, `SPFile` or `SPUser` object.
           
```php
    echo $object->getExtra('Foo'); // Bar
```
