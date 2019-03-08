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

abstract class BaseLogger {
    protected $request;
    protected $response;
    protected $log_filter;

    /**
     * Create a base logger object.
     * @param string $filter Applied log filter.
     * @return void
     */
    public function __construct($filter) {
        $this->log_filter = $filter;
    }
    /**
     * Create log models.
     * Create Request and Response objects, filled with corresponding data.
     * @return void
     */
    protected function create_log_models() {
        $filters = $this->process_filters();
        $this->request = new Request($filters["request_filters"]);
        $this->response = new Response($filters["response_filters"]);
    }

    /**
     * Process filters.
     * Sort all filters into Request and Response filters.
     * @return array Sorted filter array.
     */
    private function process_filters() {
        $request_filters = [ ];
        $response_filters = [ ];
        /* Go through all proposed filters and sort them. */
        $filters = (new ParameterFilter($this->log_filter))->create_filters();
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
     * Base logging function.
     * @abstract
     */
    public abstract function log();
}