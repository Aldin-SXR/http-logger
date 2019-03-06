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
        $log = json_encode(array_merge($this->request->get_properties(), $this->response->get_properties()));
        return $log;
    }
}