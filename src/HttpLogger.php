<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog;
use HttpLog\HttpModels\Request;

class HttpLogger {
    private $request;

    public function __construct() {
        $this->request = new Request();
    }

    public function echo_request() {
        return $this->request->get_properties();
    }
}