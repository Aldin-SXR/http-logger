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
        register_shutdown_function("HttpLog\Errors\ErrorHandler::handle_fatal_errors");
    }

    /**
     * Error handler.
     * The custom error-handling function.
     * @return void
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
            $this->output_fatal_error($error);
        }
    }

    /**
     * Handle fatal errors.
     * The code for handling otherwise uncatchable fatal PHP errors.
     * @return void
     */
    public static function handle_fatal_errors() {
        $error = error_get_last();
        $error_data = [
            "error_type" => "FATAL",
            "log_level" => LOG_ERR,
            "error_code" => 42,
            "description" => $error["message"],
            "file" => $error["file"],
            "line" => $error["line"]
        ];
        self::$logger->store_error($error_data);
        if (in_array($error["type"], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ])) {
            self::output_fatal_error($error_data, false);
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

    /**
     * Log fatal errors.
     * Log and output fatal PHP errors.
     * @param array $error Fatal error data.
     * @param boolean $echo_as_json Whether to echo the error data as JSON or not.
     * @static
     * @return void
     */
    public static function output_fatal_error($error, $echo_as_json = true) {
        http_response_code(500);
        self::$logger->log();
        /* Output error data as JSON */
        if ($echo_as_json) {
            header("Content-Type: application/json");
            die(json_encode($error));
        }
        die();
    }
}