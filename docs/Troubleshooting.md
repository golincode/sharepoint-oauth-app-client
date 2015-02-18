# Troubleshooting
Here you will find a list of common library issues and their respective resolution.

## Unable to make an HTTP request (SPException thrown)
If you bump into this one, check the stack trace for one these error messages:
```
cURL error 35: Unknown SSL protocol error in connection to accounts.accesscontrol.windows.net:443
```
```
cURL error 4: OpenSSL was built without SSLv2 support
```

Depending on the **libcURL** version your PHP is currently using, either use SSL **version 3** (`CURL_SSLVERSION_SSLv3`) or TLS **version 1.0** (`CURL_SSLVERSION_TLSv1_0`) to overcome the situation.
That can be achieved through the [SPSite](docs/SPSite.md) settings when creating a new instance:

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
