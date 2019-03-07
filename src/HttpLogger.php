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
     * Create the incoming request/response logger.
     * Choose the log type and additional log parameters.
     * @param string $type Log type (file, MySQL or MongoDB).
     * @param string $filter Filter defintion (which properties will be logged).
     * @param string $path Log path.
     * @return BaseLogger A subtype of a logger.
     */
    public static function create($type = "file", $filter = "standard", $path) {
        switch ($type) {
            case "file":
                return new FileLogger($path, $filter);
            default:
                throw new \Exception("Unrecognized log type.");
        }
    }
}