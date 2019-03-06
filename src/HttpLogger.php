<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog;
use HttpLog\HttpModels\Request;
use HttpLog\HttpModels\Response;

class HttpLogger {
    private $request;
    private $response;

    /**
     * Construct the logger object.
     * @return void
     */
    public function __construct() {
        $this->request = new Request();
        $this->response = new Response;
    }

    public function log() {
        $log = array_merge($this->request->get_properties(), $this->response->get_properties());
        $log = $this->format_output($log);
        return $log;
    }

    /**
     * Format output.
     * Format the desired log output.
     * @param array $log Log array.
     * @return string Formatted log string.
     */
    private function format_output($log) {
        foreach ($log as &$log_item) {
            /* Format arrays into JSON strings */
            if (is_array($log_item)) {
                $log_item = json_encode($log_item);
            }
        }
        /* Implode the array into a tab-separated string */
        $log = implode("\t", $log);
        return $log;
    }
}