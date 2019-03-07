<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Filters;

/**
 * A list of pre-defined log filters.
 */
class DefaultFilters {

    /** @var array STANDARD "Standard" (necessary) log array */
    const STANDARD = [
        "date", "ip", "method", "url", "query", "data", "code", "body"
    ];
    
    /** @var array FULL All log array parameters. */
    const FULL = [
        "date", "base", "url", "referrer", "method", "ip", "port", "scheme", "user_agent", "type", "length", "accept", "query", "data", "cookies", "files", "is_https", "ajax", "request_headers", "code", "body", "response_headers"
    ];
    
    /** @var array REQUEST_ONLY All request log parameters. */
    const REQUEST_ONLY = [
        "date", "base", "url", "referrer", "method", "ip", "port", "scheme", "user_agent", "type", "length", "accept", "query", "data", "cookies", "files", "is_https", "ajax", "request_headers"
    ];

    /** @var array RESPONSE_ONLY All response log parameters. */
    const RESPONSE_ONLY = [
        "code", "body", "response_headers"
    ];
}