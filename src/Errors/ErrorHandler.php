<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Errors;

use HttpLog\Loggers\BaseLogger;


 /**
  * Code insipration and logic taken from: http://php.net/manual/en/function.set-error-handler.php 
  */
class ErrorHandler {
    /** @var BaseLogger $logger Logger type. */
    private static $logger;

    /**
     * Create the error handler object.
     * @return void 
     */
    public static function create($logger) {
        self::$logger = $logger;
        set_error_handler("HttpLog\Errors\ErrorHandler::handle_error");
    }

    /**
     * Error handler.
     * The custom error-handling function.
     */
    public static function handle_error($code, $description, $file = NULL, $line = NULL) {
        $error = self::map_error($code);
        $error_data = [
            "error_type" =>$error["error_type"],
            "log_level" => $error["log_level"],
            "error_code" => $code,
            "description" => $description,
            "file" => $file,
            "line" => $line,
        ];
        /* Log error data */
        self::$logger->store_error($error_data);
        /* End script execution for fatal errors. */
        /* If a notice or warning is encountered, the script execution will continue, and the logger will work as intended.
            If a fatal error is encountered, the script execution will terminate, making it a necessity to call the log() method one more time.
        */
        if ($error["error_type"] === "FATAL") {
            http_response_code(500);
            header("Content-Type: application/json");
            self::$logger->log();
            die(json_encode($error_data));
        }
    }

    /**
     * Map errors.
     * Using the error code, determine the type and severity of error.
     * @param int $code Error code.
     * @return array Error severity and type.
     */
    private static function map_error($code) {
        /* Determine error type and severity */
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return [
                    "error_type" => "FATAL",
                    "log_level" => LOG_ERR
                ];
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                return [
                    "error_type" => "WARNING",
                    "log_level" => LOG_WARNING
                ];
            case E_NOTICE:
            case E_USER_NOTICE:
                return [
                    "error_type" => "NOTICE",
                    "log_level" => LOG_NOTICE
                ];
            case E_STRICT:
                return [
                    "error_type" => "STRICT",
                    "log_level" => LOG_NOTICE
                ];
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return [
                    "error_type" => "DEPRECATED",
                    "log_level" => LOG_NOTICE
                ];
            default: 
                break;
        }
    }
}