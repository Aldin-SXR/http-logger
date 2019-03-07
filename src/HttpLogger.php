<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog;
use HttpLog\Loggers\FileLogger;

class HttpLogger {
    /**
     * Log the incoming request/response.
     * Choose the log type and additional log paramters.
     * @param string $type Log type (file, MySQL or MongoDB).
     * @param string $path Log path.
     * @return int|boolean Logger response.
     */
    public static function log($type = "file", $path) {
        switch ($type) {
            case "file":
                return (new FileLogger($path))->log();
            default:
                throw new \Exception("Unrecognized log type.");
        }
    }
}