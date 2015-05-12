# SharePoint Properties
The `SPPropertiesTrait` contains methods related to common properties of SharePoint objects.

## Classes using this trait
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)

## GUID
Get the GUID of a `SPList`, `SPItem`, `SPFolder` or `SPFile` object.

```php
echo $object->getGUID(); // 00000000-0000-ffff-0000-000000000000
```

## Title
Get the title of a `SPList`, `SPItem`, `SPFolder` or `SPFile` object.

```php
echo $object->getTitle(); // Some Title
```

## Type
Get the SharePoint type of a `SPList`, `SPItem`, `SPFolder` or `SPFile` object.

```php
echo $object->getType(); // SP.Folder
```
