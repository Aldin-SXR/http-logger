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
        // TODO: Create a better switching mechanism (DRY)
        switch ($this->filter) {
            case "standard":
                return explode("|", DefaultFilters::STANDARD);
            case "full":
                return explode("|", DefaultFilters::FULL);
            case "full+h":
                return explode("|", DefaultFilters::FULL_H);
            case "request_only":
                return explode("|", DefaultFilters::REQUEST_ONLY);
            case "request_only+h":
                return explode("|", DefaultFilters::REQUEST_ONLY_H);
            case "response_only":
                return explode("|", DefaultFilters::RESPONSE_ONLY);
            case "response_only+h":
                return explode("|", DefaultFilters::RESPONSE_ONLY_H);
            default:
                return $this->parse_custom_filters();
        }
    }

    /**
     * Parse custom filters.
     * Take a custom user filter and parse its parameters.
     * @return array Array of filter parameters.
     */
    private function parse_custom_filters() {
        /* Try to split and parse a custom filter */
        try {
            return explode("|", $this->filter);
        } catch (\Throwable $e) {
            throw new \Exception("Invalid filter provided.");
        }
    }
}