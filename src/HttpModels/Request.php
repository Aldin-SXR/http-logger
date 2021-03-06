<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\HttpModels;
use HttpLog\Utils\HeaderUtils;

class Request {
    /** @var array $filter Array of filtered Request properties. */
    private $filter;
    private $date;
    private $base;
    private $url;
    private $referrer;
    private $method;
    private $ip;
    private $port;
    private $scheme;
    private $user_agent;
    private $type;
    private $length;
    private $accept;
    private $query;
    private $data;
    private $cookies;
    private $files;
    private $is_https;
    private $is_ajax;
    private $request_headers;

    /**
     * Construct the Request object.
     * @param array $filters Array of filtered Request properties.
     * @return void
    */
    public function __construct($filters) {
        $this->filters = $filters;
        $properties = [
            "date" => date('Y-m-d H:i:s'),
            "base" => str_replace(array("\\"," "), array("/","%20"), dirname($this->get_variable('SCRIPT_NAME'))),
            "url" => str_replace("@", "%40", $this->get_variable("REQUEST_URI", "/")),
            "referrer" => $this->get_variable("HTTP_REFERER"),
            "method" => $this->get_method(),
            "ip" => $this->get_variable("REMOTE_ADDR"),
            "port" => $this->get_variable("REMOTE_PORT"),
            "scheme" => $this->get_variable("SERVER_PROTOCOL", "HTTP/1.1"),
            "user_agent" => $this->get_variable("HTTP_USER_AGENT"),
            "type" => $this->get_variable("CONTENT_TYPE"),
            "length" => $this->get_variable("CONTENT_LENGTH", 0),
            "accept" => $this->get_variable("HTTP_ACCEPT"),
            "query" => $_GET,
            "data" => $_POST,
            "cookies" => $_COOKIE,
            "files" => $_FILES,
            "is_https" => $this->get_variable("HTTPS", "off") === "on" ? 1 : 0,
            "is_ajax" => $this->get_variable("HTTP_X_REQUESTED_WITH") === "XMLHttpRequest" ? 1 : 0,
            "request_headers" => HeaderUtils::get_request_headers()
        ];
        /* Initialize the Request object */
        $this->initialize($properties);
    }

    /**
     * Initialize Request properties.
     * @param array $properties Array of request properties.
     * @return void
     */
    private function initialize($properties) {
        /* Set all defined properties */
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
        /* Perform additional formatting */
        $this->format_url();
        $this->format_json_request_body();
    }

    /**
     * Get a request variable.
     * Get a request variable from $_SERVER, or use a $default value.
     * @param string $variable Variable name.
     * @param string $default Default value to be used.
     * @return string Variable value.
     */
    private function get_variable($variable, $default = "") {
        return isset($_SERVER[$variable]) ? $_SERVER[$variable] : $default;
    }

    /**
     * Get the request method.
     * @return string
     */
    private function get_method() {
        $method = $this->get_variable("REQUEST_METHOD", "GET");
        /* Check for method overrides */
        if (isset($_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"])) {
            $method = $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"];
        }
        /* Check for alternative method header */
        if (isset($_REQUEST["_method"])) {
            $method = $_REQUEST["_method"];
        }
        /* Return capitalized method name */
        return strtoupper($method);
    }

    /**
     * Get request body.
     * Get the body of an HTTP request (used in case of JSON input).
     * @return string Raw HTTP request body.
     */
    private function get_request_body() {
        static $body;
        /* If a body already exists, return it*/
        if (!is_null($body)) {
            return $body;
        }
        /* Get the body from a POST, PUT or PATCH method. */
        $method = $this->get_method();
        if (in_array($method, [ "POST", "PUT", "PATCH" ])) {
            $body = file_get_contents("php://input");
        }
        /* Return request body */
        return $body;
    }

    /**
     * Format URL.
     * Perform additional formatting of URL.
     * @return string Newly formatted URL.
     */
    private function format_url() {
        /* Get the requested URL without the base directory */
        if ($this->base != "/" && strlen($this->base) > 0 && strpos($this->url, $this->base) === 0) {
            $this->url = substr($this->url, strlen($this->base));
        }
        /* Set a default URL, if necessary */
        if (empty($this->url)) {
            $this->url = '/';
        }
    }

    /**
     * Format request body (if working with JSON).
     * @return string JSON request body.
     */
    private function format_json_request_body() {
        /* Check for JSON input */
        if (strpos($this->type, "application/json") === 0) {
            $body = $this->get_request_body();
            if ($body !== "") {
                $data = json_decode($body, true);
                if ($data !== null) {
                    $this->data = $data;
                }
            }
        }
    }

    /**
     * Return Request properties.
     * @return array Array of Request properties.
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