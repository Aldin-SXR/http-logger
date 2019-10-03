# PHP HTTP Logger

A simple HTTP request, response and error logger for API projects in PHP. It can be used with different types of middleware (depending on the framework or library), as well as pure PHP projects.

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

In order to start logging HTTP requests and responses, you need to first create an `HttpLogger` object. The object constructor has three required parameters, and an optional fourth one:
1. `type`:  Type of logger (for now, _only_ the file logger is supported; SQL and MongoDB loggers are planned for addition at a latter date; _default_: `file`)
2. `filter`: Log filter. It represents which request/response properties will be logged (_default_: `full+h` = all supported properties + request and response headers) ([see more](#filters))
    - features several default filter configurations, as well as the option to define custom filters
3. `path`: Path to the log file (_default_: `logs/debug.log`)
    - Make sure that your target folder _exists_ and that you have the correct _write permissions_.
    - __Note__: Any custom path can be specifed, but it __has to__ be specified as an _absolute path_ (e.g. by using `__DIR__`) to the project root. The reason for this is that certain PHP functions which can be triggered by the library change the execution path, so we must rely on absolute paths.
4. `default_log` (_optional_): Controls whether the errors will __also__ be logged to the default Apache/PHP log file (_default_: `true`)
    - The library logs all errors to its specified `path` file by design. With the `default_log` parameter, you can also let all errors be logged to the default error log file, _in addition to_ the library's log file.
    -  __Note__: Fatal PHP errors (which are irrecoverable) are always logged to the default error log file, regardless of this parameter.

Example logger initialization looks like this:
```php
require_once __DIR__."/vendor/autoload.php";
/* Use the logger namespace */
use HttpLog\HttpLogger;

$logger = HttpLogger::create("file", "full+h", "logs/debug.log", false)::get();
```

`HttpLogger::create()` constructs a static instance of the logger (which can later be reused anywhere in the code), while `HttpLogger::get()` returns the newly created logger instance.

These methods can be chained, as seen in the example above, or called individually, like this:
```php
HttpLogger::create("file", "full+h", "logs/debug.log", false);
/* The HttpLogger::get() can then be called from anywhere inside the project. */
$logger = HttpLogger::get();
```

After logger initialization, the logger is ready to be used. Depending on your project needs and technologies used, call the `log()` method either at the end of your response outputs, or in specific middlewares that are set to fire after an API response has been sent.
- __Note__: This is the recommended behavior. The logger could theoretically be called from elsewhere before the response, but in that case, _it will not_ catch and log the response output. Moreover, additional unexpected behavior is possible.

```php
/* Pure PHP example  */

require_once __DIR__."/vendor/autoload.php";
use HttpLog\HttpLogger;

/* Create and fetch the logger instance */
$logger = HttpLogger::create("file", "full+h", "logs/debug.log", false)::get();

/* Output a response */
header("Content-Type: application/json");
echo json_encode( ["param" => "test"] );

/* Log the incoming request, outgoing response and possible errors */
$logger->log();
```

### Log format

The log is formatted as a [TSV textual file](https://whatis.techtarget.com/fileformat/TSV-Tab-separated-values-file), with all logged parameters separated by tabs (`\t`), which allows for easy parsing and formatting. Each request/response "pair" is represented by a single line in the file, starting with the _request time_, and followed by _request data_, _response data_ and any encountered _errors_. 

Example (sent with Postman):
```
2019-03-12 21:05:31	/http-logger	/?param=test	http://localhost/http-logger?param=test	GET	::1	36360	HTTP/1.1	PostmanRuntime/7.6.0		0	*/*	{"param":"test"}	[]	[]	[]	0	0	{"test_header":"test_value","cache-control":"no-cache","Postman-Token":"a84d2d58-3f77-4c39-a60d-a06487a6cdc8","User-Agent":"PostmanRuntime\/7.6.0","Accept":"*\/*","accept-encoding":"gzip, deflate","referer":"http:\/\/localhost\/http-logger?param=test","Host":"localhost","Connection":"keep-alive"}	200	{"param":"test"}	{"Keep-Alive":"timeout=5, max=99","Connection":"Keep-Alive","Transfer-Encoding":"chunked","Content-Type":"application\/json"}

```

#### Logged parameters

The following parameters are available for logging (and used in the default configuration):
- `date`: The exact time when the request occured
- `base`: The parent subdirectory of the URL
- `url`: The URL being requested
- `referrer`:  The referrer URL
- `method`:  The request method (GET, POST, PUT, DELETE, etc.)
- `ip`: IP address of the client
- `port`: Client's accessing port
- `scheme`: The server protocol (HTTP/HTTPS)
- `user_agent`: Browser information
- `type`: The content type
- `length`: The content length
- `accept`: HTTP accept parameters
- `query`: Query string parameters
- `data`: POST parameters
- `cookies`: Cookie parameters
- `files`: Uploaded files
- `is_https`: Whether the connection is secure (HTTPS)
- `is_ajax`: Whether the request is an AJAX request
- `request_headers`: HTTP request headers
- `code`: HTTP response code
- `body`: HTTP response body
- `response_headers`: HTTP response headers

#### Filters

The library allows for different __logging filters__ to be applied to the logger. The filters (specified as the _second_ parameter in the [logger creation](#basic-usage)) specify _which_ of the aforementioned parameters will be logged (and in which order).

Each built-in filter features two variants: the _base_ variant and the _headers_ variant. The base variant includes all parameters related to that filter, __except__ full header information (`request_headers` and `response_headers` are absent). The headers variant contains both the parameters and all header information.

The available (base variant) filters are:
- `standard`: includes `date`, `url`, `method`, `ip`, `query`, `data`, `code`, `body`
- `full`: all loggable properties (as seen [above](#logged-parameters))
- `request_only`: only request properties (all properties _except_ `code` and `body`)
- `response_only`: only response properties (`code` and `body`)
- `error`: __only__ logs the encountered errors (notices, warning and errors) ([see more](#the-error-filter))

Each of these filters (except `error`) __also__ features a _headers_ variant (`standard+h`, `full+h`, `request_only+h`, `response_only+h`), which includes the additional header information (request headers after request properties and response headers after response properties).

The `full+h` filter is set as the default in the library, meaning that the logger will store all request and response data, along with request and response headers.

##### Custom filters

The library allows for the creation of __custom filters__, allowing the users to set the specific properties they want to log, as well as their order.

The custom filters are defined as follows: `property1|property2|property3|...` (properties separated by a pipe symbol - `|`).

When parsed by the logger, only the properties specified in the custom filter will be logged, disregarding the rest.

Example:
```php
/* Defining a logger with a custom filter */
$logger = HttpLogger::create("file", "date|url|method|ip|", "logs/debug.log", false)::get();
```

The logger in this example will _only_ log the request date, requested URL, request method and the client's IP address, in that exact order.

### Error logging

In addition to request and response logging, the library also features _error intercepting_ and _handling_. The libary is able to log and gracefully handle PHP _notices_, _warnings_, _errors_ and _fatal errors_. The errors will be logged on the same line _after_ request and response data, as _encoded JSON strings_.
- __Note__: for the sake of simplicity, in the remainder of this document we will use the term "error" to refer to all notices, warnings and errors, if not explicitly stated otherwise.

Example:
```
2019-03-12 22:07:11	/http-logger	/?param=test	http://localhost/http-logger?param=test	GET	::1	37714	HTTP/1.1	PostmanRuntime/7.6.0		0	*/*	{"param":"test"}	[]	[]	[]	0	0	{"test_header":"test_value","cache-control":"no-cache","Postman-Token":"3a41e974-4f0c-49e6-8af3-3942ecd25f80","User-Agent":"PostmanRuntime\/7.6.0","Accept":"*\/*","accept-encoding":"gzip, deflate","referer":"http:\/\/localhost\/http-logger?param=test","Host":"localhost","Connection":"keep-alive"}	500		{"Connection":"close","Content-Type":"application\/json"}	[{"error_type":"NOTICE","log_level":5,"error_code":8,"description":"Undefined variable: undefined","file":"\/var\/www\/html\/http-logger\/index.php","line":13},{"error_type":"INFO","log_level":6,"error_code":1024,"description":"This is an informational message.","file":"\/var\/www\/html\/http-logger\/index.php","line":15},{"error_type":"FATAL","log_level":3,"error_code":1,"description":"Uncaught Error: Call to undefined function no_such_function() in \/var\/www\/html\/http-logger\/index.php:17\nStack trace:\n#0 {main}\n  thrown","file":"\/var\/www\/html\/http-logger\/index.php","line":17}]
```

#### How are errors handled?

During the request/response lifecycle, the PHP script is left to execute. The logger is setting one of its methods as the error handler (via `set_error_handler()`), and intercepting any irregular flows.

All encountered errors are stored in the logger's _error stack_ (array), and after the `log()` method is called, all errors are logged to the same line as the request and response data, allowing for an easy review of irregular flows that may have occured during execution.

Notices and warnings will be stored in the error stack, and the regular script execution will continue as normal, until the logging is performed and the script finishes execution naturally. __However__, _serious errors_ (user errors and fatal PHP errors) will, after being logged, stop the script's execution at the time of occurence and return a formatted JSON response to the user, with the details of the fatal error.

The error object contains the following parameters (all of which are logged):
- `error_type`: The type of the caught error (notice, warning, error, etc.)
- `log_level`: The error's logging level
- `error_code`: The error code
- `description`: Message description of the caught error
- `file`: The file in which the error occured
- `line`: The line in which the error occured

#### The `error` filter

Some users might only care about the encountered errors, and not necessarily about request and response data. For that reason, it is possible to apply the `error` filter during logger initialization.

With the `error` filter applied, __only__ the errors will be logged, in _TSV format_ (as opposed to formatted JSON in all other filters). Moreover, each error will be logged on a separate line, and include a `date` property.

```php
/* Create and fetch an error-only logger instance */
$logger = HttpLogger::create("file", "error", "logs/debug.log", false)::get();

print_r($undefined);
md5();
no_such_function();
```

Resulting log:
```
2019-03-12 21:55:25	NOTICE	5	8	Undefined variable: undefined	/var/www/html/http-logger/index.php	13
2019-03-12 21:55:25	WARNING	4	2	md5() expects at least 1 parameter, 0 given	/var/www/html/http-logger/index.php	14
2019-03-12 21:55:25	FATAL	3	1	Uncaught Error: Call to undefined function no_such_function() in /var/www/html/http-logger/index.php:17 Stack trace: #0 {main}   thrown	/var/www/html/http-logger/index.php	17

```

#### User-defined errors

Apart from automatically managing encountered errors, the library also provides users with the ability to _log a custom error message_ anywhere in the project (similar to how [Log4j](https://logging.apache.org/log4j/2.x/) works).

The library contains the following logging methods, which can be called from the logger object:
- `debug()`
- `info()`
- `warning()`
- `error()`
- `fatal()`

Example:
```php
/* Create and fetch the logger instance */
$logger = HttpLogger::create("file", "full+h", "logs/debug.log", false)::get();

$logger->warning("Testing a sample warning.");
$logger->info("This is an informational message.");
$logger->fatal("This fatal error will stop program execution.");
```

These user-defined errors "behave" in the same way as regular errors: debug messages, warnings and info messages are simply logged, whereas errors and fatal errors also stop program execution. Moreover, the logger will have no difficulties logging both the regular errors and the user-defined errors in a single session.
- __Note__: Since the primary purpose of the logger library is request/response logging, the user __still__ needs to call `$logger->log()` or `HttpLogger::get()->log()` somewhere in the code. The user-defined functions will _only_ store the logs within the logger object. The `log()` method is the one that actually commits the logs to a file.

If the `default_log` parameter in the logger initialization is set to `true`, user-defined errors will also be logged in the default Apache/PHP log file.

```php
/* Create and fetch an error-only logger instance */
$logger = HttpLogger::create("file", "error", "logs/debug.log", false)::get();

/* A mix of regular and user-defined errors */
$logger->info("This is an informational message.");
print_r($undefined_variable);
$logger->fatal("This is a fatal error.");
```

Resulting log:
```
2019-03-12 22:15:55	INFO	6	1024	This is an informational message.	/var/www/html/http-logger/index.php	15
2019-03-12 22:15:55	NOTICE	5	8	Undefined variable: undefined_variable	/var/www/html/http-logger/index.php	13
2019-03-12 22:15:55	FATAL	3	256	This is a fatal error.	/var/www/html/http-logger/index.php	16

```

### Additional information and notes
- It is highly recommended __not__ to use `ini_set('display_errors', 1)`, when using this library, because this setting will cause all errors to go to the response body buffer, polluting your response logs. If you _do not care_ about response logging, however, feel free to use this setting.
- Our recommendation is to disable the default PHP logging by setting the `default_log` parameter to `false` during logger initialization, as all information (even more detailed than the default logger) will be kept in the library's log file, and we wish to avoid data redundancy. However, you can use both loggers together without issues, should your needs require.

## Library documentation

Extensive documentation can be found at: [https://aldin-sxr.github.io/http-logger/](https://aldin-sxr.github.io/http-logger/)

The documentation was generated with [PHPDocumentor](https://www.phpdoc.org/).

## Authors
- __Aldin Kovačević__, _initial work on the library and documentation_ - [Aldin-SXR](https://github.com/Aldin-SXR)

## Acknowledgements
- __Adnan Miljković__, for his professional advice and suggestions on how to implement certain features - [adnanmiljkovic](https://github.com/adnanmiljkovic)
- __Dino Kečo__, for encouraging me to create this library in the first place - [dinokeco](https://github.com/dinokeco)

## License
The skeleton is licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) license. See the [LICENSE](https://github.com/Aldin-SXR/http-logger/blob/master/LICENSE) file for details.
  
---
_Work in progress_ by [_tribeOS - The Fairest, Most Profitable Advertising Marketplace Ever._](http://tribeos.io)
