<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog;
use HttpLog\Loggers\FileLogger;
use HttpLog\Errors\ErrorHandler;

class HttpLogger {
    /** @var BaseLogger The logger instance. */
    private static $logger;

    /**
     * Create the incoming request/response logger.
     * Choose the log type and additional log parameters.
     * @param string $type Log type (file, MySQL or MongoDB).
     * @param string $filter Filter defintion (which properties will be logged).
     * @param string $path Log path.
     * @param boolean $default_log Whether to also log all errors to PHP's default error log file.
     * @return HttpLogger A subtype of a logger.
     */
    public static function create($type = "file", $filter = "standard", $path, $default_log = true) {
        /* Register the error handler. */
        ErrorHandler::create();
        switch ($type) {
            case "file":
                self::$logger = new FileLogger($filter, $path, $default_log);
                break;
            default:
                throw new \Exception("Unrecognized log type.");
        }
        return new self();
    }

    /**
     * Get the logger instance.
     * Return an instance of the previously created logger.
     * @static
     * @return BaseLogger The logger instance.
     */
    public static function get() {
        return self::$logger;
    }
}