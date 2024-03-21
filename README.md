# AirLST Headless Browser PHP Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/airlst/headless-browser-client-php.svg?style=flat-square)](https://packagist.org/packages/airlst/headless-browser-client-php)
[![Total Downloads](https://img.shields.io/packagist/dt/airlst/headless-browser-client-php.svg?style=flat-square)](https://packagist.org/packages/airlst/headless-browser-client-php)
![GitHub Actions](https://github.com/airlst/headless-browser-client-php/actions/workflows/main.yml/badge.svg)

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
$headlessBrowser = new \AirLST\HeadlessBrowserClient\AirlstHeadlessBrowser($httpClient, 'api-key-here');
```

### Generate PDF from HTML

```php
$headlessBrowser->pdf(
    '<p>html</p>', // html content
    'A4', // page size
    [10, 10, 10, 10] // margins
);
```

Returns `\Airlst\HeadlessBrowserClient\Response\PdfResponse` object.
You can access `contents()` method on this object to get PDF contents as string.

### Generate JPEG from HTML

```php
$headlessBrowser->jpeg(
    '<p>html</p>', // html content
    80, // quality
);
```

Returns `\Airlst\HeadlessBrowserClient\Response\JpegResponse` object.
You can access `contents()` method on this object to get JPEG contents as string.

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
