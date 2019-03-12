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

    /** @var string STANDARD "Standard" (necessary) log string */
    const STANDARD = "date|url|method|ip|url|query|data|code|body";
    
    /** @var string FULL All log string parameters. */
    const FULL = "date|base|url|referrer|method|ip|port|scheme|user_agent|type|length|accept|query|data|cookies|files|is_https|is_ajax|code|body";

    /** @var string FULL All log string parameters + headers. */
    const FULL_H = "date|base|url|referrer|method|ip|port|scheme|user_agent|type|length|accept|query|data|cookies|files|is_https|is_ajax|request_headers|code|body|response_headers";    
    
    /** @var string REQUEST_ONLY All request log parameters. */
    const REQUEST_ONLY = "date|base|url|referrer|method|ip|port|scheme|user_agent|type|length|accept|query|data|cookies|files|is_https|is_ajax";

    /** @var string REQUEST_ONLY All request log parameters + request headers. */
    const REQUEST_ONLY_H = "date|base|url|referrer|method|ip|port|scheme|user_agent|type|length|accept|query|data|cookies|files|is_https|is_ajax|request_headers";

    /** @var string RESPONSE_ONLY All response log parameters. */
    const RESPONSE_ONLY = "code|body";

    /** @var string RESPONSE_ONLY All response log parameters + response headers. */
    const RESPONSE_ONLY_H = "code|body|response_headers";

    /** @var string ERROR_ONLY Only log errors.  */
    const ERROR_ONLY = "error";
}