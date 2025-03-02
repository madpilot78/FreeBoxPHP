# FreeBox PHP

[![Packagist Version](https://img.shields.io/packagist/v/madpilot78/freebox-php)](https://packagist.org/packages/madpilot78/freebox-php)
[![Actions Status](https://img.shields.io/github/actions/workflow/status/madpilot78/FreeBoxPHP/tests.yml)](https://github.com/madpilot78/FreeBoxPHP/actions/workflows/tests.yml)
[![codecov](https://codecov.io/github/madpilot78/FreeBoxPHP/graph/badge.svg?token=PAAARJT32F)](https://codecov.io/github/madpilot78/FreeBoxPHP)

This is a small and simple PHP library to interface with Iliad FreeBox
(IliadBox for the Italian market) provided broadband routers.

## Install

Using composer:

```sh
composer require madpilot78/FreeBoxPHP
```

## Requirements

This project works with PHP 8.3 and upper.

## Library description

I'm writing this library for my own needs. It handles discovery and
authentication and then exposes some router APIs via methods.

For a list of implemented methods look in `src/Methods`.

NOTE: Since I'm in Italy I have tested this only with my router, an
IliadBox (Italian version), configured with self provided certificate.
If you have a different setup and have problems, please contact me
and maybe I can update the library to work with more setups.

### NOTE

For documentation on each API specifics please check your OpenBox/IliadBox
developer documentation, accessible through the WebUI.

## How to use

There is a `Configuration` object that can be used to customize the library.

First one needs to register with the `Box`, for example:

```php
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\BoxType;

$config = new Configuration(
    hostname: 'box.example.org',
    boxType: BoxType::Iliad,
    certFile: null,
);

$box = new Box(configuration: $config);

$token = $box->discover()->register(quiet: false);

echo $token . PHP_EOL;
```

(check the FreeBox/IliadBox display to authorize the client)

Once the client has been authorized its permissions can be configured in
the FreeBox/IliadBox UI.

With the token it is possible to access all the provided functionality.

For example to display IPv6 configuration:

```php
use madpilot78\FreeBoxPHP\Configuration;
use madpilot78\FreeBoxPHP\Box;
use madpilot78\FreeBoxPHP\Enum\BoxType;

$config = new Configuration(
    hostname: 'box.example.org',
    boxType: BoxType::Iliad,
    certFile: null,
);

$box = new Box(authToken: '<token>', configuration: $config);

$ret = $box->discover()->connectionIPv6Configuration('get');

var_dump($ret);
```

(NOTE: `discover()` needs to be called only once per instance, results are cached
in the instance)

The IPv6 firewall can be turned on with (this API will also return the new configuration):

```php
$ret = $box->connectionIPv6Configuration('set', ['ipv6_firewall' => true]);

var_dump($ret);
```

APIs requiring an ID take it as an argument, for example to fetch an existing redirect:

```php
$ret = $box->fwRedir('get', 1);

var_dump($ret);
```

And a disabled redirect can be modified (for example enabled) like this:

```php
$ret = $box->fwRedir('update', 1, ['enabled' => true]);

var_dump($ret);
```

## Implemented APIs

- Discover
- Register
- Login/Logout (login performed automatically if needed)
- Language (changing the language may not work, I suspect this is a
  restriction on my IliadBox)
- ConnectionConfiguration
- ConnectionIPv6Configuration
- ConnectionStatus (r/o)
- LanBrowserInterface (r/o)
- LAN WOL
