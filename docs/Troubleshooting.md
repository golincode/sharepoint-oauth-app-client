# Troubleshooting
Here you will find a list of common library issues and their respective resolution.

## Unable to make an HTTP request (SPException thrown)
You might bump into this one, depending on the **libcURL** version PHP is currently using.
To solve the problem, cURL must use SSL **version 3**.
That can be achieved through the [SPSite](docs/SPSite.md) settings when creating a new instance.

```php
$settings = [
	// SharePoint Site credentials
	'site' => [
		// ...
	],

	// set cURL to use SSL v3
	'http' => [
		'defaults' => [
			'config' => [
				'curl' => [
					CURLOPT_SSLVERSION => 3
				]
			]
		]
	]
];
```
