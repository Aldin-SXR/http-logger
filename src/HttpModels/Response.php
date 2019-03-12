<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\HttpModels;
use HttpLog\Utils\HeaderUtils;

class Response {
    /** @var array $filter Array of filtered Request properties. */
    private $filter;
    private $code;
    private $body;
    private $response_headers;

    /**
     * Construct the Response object.
     * @param array $filters Array of filtered Response properties.
     * @return void
    */
    public function __construct($filters) {
        $this->filters = $filters;
        $properties = [
            "code" => http_response_code(),
            "body" => $this->get_body(),
            "response_headers" => HeaderUtils::get_response_headers()
        ];
        /* Initialize the Request object */
        $this->initialize($properties);
    }

    /**
     * Initialize Response properties.
     * @param array $properties Array of response properties.
     * @return void
     */
    private function initialize($properties) {
        /* Set all defined properties */
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Get response body.
     * Get (if possible, formatted) response body.
     * @return array|string Response body.
     */
    private function get_body() {
        $response_contents = ob_get_contents();
        /* Try to decode the output as a JSON, if possible; if not, return raw response body. */
        if ($decoded_response = json_decode($response_contents, true)) {
            return $decoded_response;
        }
        /* Return response body */
        return str_replace("\n", " ", $response_contents);
    }

    /**
     * Return Response properties.
     *  @var string $criteria
     * @return array Array of Response properties.
     */
    public function get_properties() {
        $properties = [ ];
        foreach ($this->filters as $property) {
            $properties[$property] = $this->$property;
        }
        /* Return property array */
        return $properties;
    }
}