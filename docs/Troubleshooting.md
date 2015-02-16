# Troubleshooting
Here you will find a list of common library issues and their respective resolution.

## Unable to make an HTTP request (SPException thrown)
You might bump into this one, depending on the **libcURL** version your PHP is currently using.

Some of the problems might be:
```
cURL error 35: Unknown SSL protocol error in connection to accounts.accesscontrol.windows.net:443
```
```
cURL error 4: OpenSSL was built without SSLv2 support
```

To make it work, cURL must either use SSL **version 3** or TLS **version 1.0** (if supported by your PHP version).

That can be achieved through the [SPSite](docs/SPSite.md) settings when creating a new instance.

```php
$settings = [
	// SharePoint Site credentials
	'site' => [
		// ...
	],

	// configure cURL to use SSL v3 or TLS v1.0
	'http' => [
		'defaults' => [
			'config' => [
				'curl' => [
					CURLOPT_SSLVERSION => CURL_SSLVERSION_SSLv3,
					CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_0, // Available since PHP 5.5.19 and 5.6.3 / cURL 7.34+
				]
			]
		]
	]
];
```
