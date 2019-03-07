<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Filters;
use HttpLog\Filters\DefaultFilters;

class ParameterFilter {
    /** @var string $filter Log filter definition. */
    private $filter;
    /** @var boolean $include_headers Whether request and response headers should be included in the log. */
    private $include_headers;

    /**
     * Create a filtering object.
     * @param string $filter Applied log filter.
     * @return void
     */
    public function __construct($filter = "standard", $include_headers = true) {
        $this->filter = $filter;
        $this->include_headers = $include_headers;
    }

    /**
     * Create filters.
     * Create Request and Response filter arrays.
     * @return array Array of filter parameters.
     */
    public function create_filters() {
        switch ($this->filter) {
            case "standard":
                return DefaultFilters::STANDARD;
            case "full":
                return DefaultFilters::FULL;
            case "request_only":
                return DefaultFilters::REQUEST_ONLY;
            case "response_only":
                return DefaultFilters::RESPONSE_ONLY;
            default:

        }
    }
}