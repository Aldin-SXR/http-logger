# PHP HTTP Logger

A simple HTTP request/response logger for API projects in PHP. It can be used in different types of middleware (depending on the framework or library), as well as pure PHP projects.

## Getting started

### Prerequisites
This guides assumes that you are currently using (or planning to use) the following technologies in your project:
- PHP 7.1+
- [Composer](https://getcomposer.org/)

### Installation
1. First, make sure that you have installed and configured a running web server (such as Apache) with PHP 7.1 or higher.
2. Use `composer` to install the library:
    `composer require tribeos/http-log`

### Basic usage
Before using the library, you first have to require the generated `autoload.php` file:
```php
require_once "vendor/autoload.php";
```
After that, you can use the `HttpLog\*` namespace and functions you need in your project.

In order to start logging HTTP requests and responses, you need to first create an `HttpLogger` object. The object constructor has three parameters:
1. `type`:  Type of logger (for now, _only_ file logger is supported, SQL and MongoDB loggers will be added at a latter date)
2. `filter`: Log filter. It show which request/response properties will be logged.
    - features several default filter configurations, as well as the option to define custom filters
3. `path` Path to the log file.

## Authors
- __Aldin Kovačević__, _initial work on the library and documentation_ - [Aldin-SXR](https://github.com/Aldin-SXR)

## License
The skeleton is licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) license. See the [LICENSE](https://github.com/Aldin-SXR/http-logger/blob/master/LICENSE) file for details.

---
_Work in progress_ by [_tribeOS - The Fairest, Most Profitable Advertising Marketplace Ever._](http://tribeos.io)