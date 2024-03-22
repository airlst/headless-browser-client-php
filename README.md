# AirLST Headless Browser PHP Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/airlst/headless-browser-client-php.svg?style=flat-square)](https://packagist.org/packages/airlst/headless-browser-client-php)
[![Total Downloads](https://img.shields.io/packagist/dt/airlst/headless-browser-client-php.svg?style=flat-square)](https://packagist.org/packages/airlst/headless-browser-client-php)

PHP Client to interact with AirLST Headless Browser service.

## Installation

You can install the package via composer:

```bash
composer require airlst/headless-browser-client-php
```

## Usage

Initialize the headless browser client with Guzzle HTTP client and your API key.

```php
$httpClient = new \GuzzleHttp\Client();
$headlessBrowser = new \AirLST\HeadlessBrowserClient\AirlstHeadlessBrowser('api-key-here', $httpClient);
```

### Generate PDF from HTML

```php
$headlessBrowser->pdf(
    '<p>html</p>', // html content
    'A4', // page size
    [10, 10, 10, 10] // margins
);
```

### Generate JPEG from HTML

```php
$headlessBrowser->jpeg(
    '<p>html</p>', // html content
    80, // quality
);
```

### Response

All methods return `\Airlst\HeadlessBrowserClient\Response` object.
Provides 2 public methods:

- `temporaryUrl()` - returns temporary file URL stored in S3 bucket. Expires in 5 minutes after generation
- `contents()` - downloads file from S3 bucket and returns contents of the file as string

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hey@orkhan.dev instead of using the issue tracker.

## Credits

-   [Orkhan Ahmadov](https://github.com/airlst)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
