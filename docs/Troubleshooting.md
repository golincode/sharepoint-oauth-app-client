# Troubleshooting

## Unable to make an HTTP request (SPException thrown)
You might get this issue on some versions of **PHP** / **libcURL**.
To solve the problem, the cURL SSL version **3** must be enforced.
That can be achieved through the [SPSite](docs/SPSite.md) settings when creating a new instance:

```php
$settings = [
	// SharePoint Site credentials
	'site' => [
		// ...
	],

	// make cURL use SSL v3
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
