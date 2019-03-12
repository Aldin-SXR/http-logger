<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Utils;

class HeaderUtils {
    /**
     * Get request headers.
     * Get all request headers using the predefined method, or a custom method, if it doesn't exist.
     * @param boolean $as_json Whether to return the request headers as formatted JSON string or an array.
     * @return array|string Request headers.
     */
    public static function get_request_headers($as_json = false) {
         /* Get all request headers */
        if (!function_exists("getallheaders")) {
            function getallheaders() {
                $headers = []; 
                foreach ($_SERVER as $name => $value) { 
                    if (substr($name, 0, 5) == "HTTP_")  { 
                        $headers[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))))] = $value; 
                    } 
                } 
                return $headers; 
            }
        }
        /* Call the existing or custom-defined function*/
        return $as_json ? json_encode(getallheaders()) : getallheaders();
    }

    /**
     * Get response headers.
     * Get all request headers using the predefined method, or a custom method, if it doesn't exist.
     * @param boolean $as_json Whether to return the response headers as formatted JSON string or an array.
     * @return array|string Response headers.
     */
    public static function get_response_headers($as_json = false) {
        /* Get all response headers */
        if (!function_exists("apache_response_headers")) {
            function apache_response_headers () {
                $arh = array();
                $headers = headers_list();
                foreach ($headers as $header) {
                    $header = explode(":", $header);
                    $arh[array_shift($header)] = trim(implode(":", $header));
                }
                return $arh;
            }
        }
        /* Call the existing or custom-defined function*/
        return $as_json ? json_encode(apache_response_headers()) : apache_response_headers();
    }
}