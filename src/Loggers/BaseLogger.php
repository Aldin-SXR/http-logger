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

abstract class BaseLogger {
    protected $request;
    protected $response;

    /**
     * Create log models.
     * Create Request and Response objects, filled with corresponding data.
     * @return void
     */
    protected function create_log_models() {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Base logging function.
     * @abstract
     */
    public abstract function log();
}