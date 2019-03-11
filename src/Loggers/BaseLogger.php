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
            $this->request = new Request($filters["request_filters"]);
            $this->response = new Response($filters["response_filters"]);
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
     * Base logging function.
     * @abstract
     */
    public abstract function log();
}