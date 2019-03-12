<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Loggers;
use HttpLog\HttpModels\Request;
use HttpLog\HttpModels\Response;
use HttpLog\Filters\ParameterFilter;
use HttpLog\Filters\DefaultFilters;
use HttpLog\Errors\ErrorHandler;
use HttpLog\HttpModels\Error;

/**
 * Base logger class, which serves as the backbone of other loggers.
 * 
 * @method void debug(string $message) Log a debug string.
 * @method void info(string $message) Log a info string.
 * @method void warning(string $message) Log a warning.
 * @method void error(string $message) Log an error.
 * @method void fatal(string $message) Log a fatal error.
 */
abstract class BaseLogger {
    /** @var Request $request The Request object to be logged. */
    protected $request;
    /** @var Request $request The Response object to be logged. */
    protected $response;
    /** @var string $log_filter The type of applied log filter. */
    protected $log_filter;
    /** @var array $error Error data log. */
    protected $errors = [ ];

    /**
     * Create a base logger object.
     * @param string $filter Applied log filter.
     * @return void 
     */
    public function __construct($filter) {
        $this->log_filter = $filter;
        ErrorHandler::create($this);
    }
    /**
     * Create log models.
     * Create Request and Response objects, filled with corresponding data.
     * @return void
     */
    protected function create_log_models() {
        $filters = $this->process_filters();
        /* Check if the "only log errors" filter had been checked. */
        if ($filters !== "error") {
            /* Check for fatal errors: if an error is fatal, it should be outputted as JSON later, so the headers should not be flushed */
            $flush_headers = true;
            foreach ($this->errors as $error) {
                if ($error["error_type"] === "FATAL") {
                    $flush_headers = false;
                    break;
                }
            }
            $this->request = new Request($filters["request_filters"]);
            $this->response = new Response($filters["response_filters"], $flush_headers);
        }
    }

    /**
     * Process filters.
     * Sort all filters into Request and Response filters.
     * @return array Sorted filter array.
     */
    private function process_filters() {
        $request_filters = [ ];
        $response_filters = [ ];
        $filters = (new ParameterFilter($this->log_filter))->create_filters();
        /* Check if the "only log errors" filter had been checked. */
        if ($filters === "error") {
            return "error";
        }
        /* Go through all proposed filters and sort them. */
        foreach ($filters as $filter) {
            /* Check for non-existing filters*/
            if (!property_exists(Request::class, $filter) && !property_exists(Response::class, $filter)) {
                throw new \Exception("Invalid filter(s) provided.");
                die;
            }
            /** Check for response log filters. */
            if (in_array($filter, explode("|", DefaultFilters::RESPONSE_ONLY_H))) {
                $response_filters[ ] = $filter;
            } else {
                $request_filters[ ] = $filter;
            }
        }
        /* Return sorted filters */
        return [
            "request_filters" => $request_filters,
            "response_filters" => $response_filters
        ];
    }

    /**
     * Store the error.
     * Store the error data associated with a faulty request in an error array.
     * @param array $error_data Error data.
     * @return void
     */
    public function store_error($error_data) {
        $this->errors[ ] = $error_data;
    }

    /**
     * Dynamically call custom logging.
     * Based on the method name (level), allow a user to manually log an error.
     * @param string $name Name of the called method.
     * @param array $arguments Arguments of the called method.
     * @return void
     */
    public function __call($name, $arguments) {
        /* Get the current backtrace (in order to extract the file and line number). */
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = array_shift($backtrace);
        /* Create and add the new error to the error stack */
        $error_map = $this->map_error($name);
        $error = new Error(strtoupper($name), $error_map[0], $error_map[1], $arguments[0], $caller["file"], $caller["line"]);
        $error_data = $error->get_properties();
        $this->errors[ ] = $error_data;
        /* End script execution on fatal error */
        if ($error_data["error_type"] === "FATAL") {
            ErrorHandler::output_fatal_error($error_data, true);
            die();
        }
    }

    /**
     * Map errors.
     * Map user-defined errors to their respective log levels and error codes.
     * @param string $name Logging level name.
     * @return array Log level and error code.
     */
    private function map_error($name) {
        switch ($name) {
            case "debug":
                return [ LOG_DEBUG, E_USER_NOTICE ];
            case "info":
                return [ LOG_INFO, E_USER_NOTICE ];
            case "warning":
                return [ LOG_WARNING, E_USER_WARNING ];
            case "error": 
                return [ LOG_ERR, E_USER_ERROR ];
            case "fatal":
                return [ LOG_ERR, E_USER_ERROR ];
            default:
                throw new \Exception("Invalid method called.");
        }
    }

    /**
     * Base logging function.
     * @abstract
     */
    public abstract function log();
}