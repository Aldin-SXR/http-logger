<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\Loggers;
use HttpLog\Loggers\BaseLogger;

class FileLogger extends BaseLogger {
    /** @var string $log_file Path to log file. */
    private $log_file;

    /**
     * Construct the file logger.
     * 
     * @param string $filter Applied log filter.
     * @param string $path File log path.
     * @param boolean $default_log Whether to also log all errors to PHP's default error log file.
     * @return void
     */
    public function __construct($filter, $path, $default_log) {
        parent::__construct($filter, $default_log);
        /* Absolute log path should be used in input. */
        $this->log_file = $path;
    }

    /**
     * Log the incoming request/response pair.
     * Logs the incoming request and corresponding response into a file.
     * @return void
     */
    public function log() {
        if ($this->log_filter === "error") {
            /* Error-only logging */
            $this->log_errors();
        } else {
            /* Create Response and Request objects */
            $this->create_log_models();
            /* Perform the logging*/
            $log = array_merge($this->request->get_properties(), $this->response->get_properties());
            $this->errors ? $log[ ] = json_encode($this->errors) : $log;
            $log = $this->format_output($log);
            $this->write_to_file($log);
        }
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

    /**
     * Write to file.
     * Write the contents of a variable to the file specified by a path.
     * @param string $content Content to be written to the file.
     * @return void
     */
    private function write_to_file($content) {
        try {
            file_put_contents($this->log_file, $content."\n", FILE_APPEND);
        } catch (\Throwable $e) {
            $e->getTraceAsString();
        }
    }

    /**
     * Error-only logging.
     * Called when the filter parameter is set to "error"; it only logs errors, without the request and response.
     * @return void
     */
    private function log_errors() {
        foreach ($this->errors as &$error) {
            $error = array_merge([ "date" => date('Y-m-d H:i:s') ], $error); 
            $error = implode("\t", $error);
            // TODO: Figure out if there is a better way of dealing with error messages.
            //  $error["description"] = explode("\n", $error["description"])[0];
            $error = str_replace("\n", " ", $error);
            $this->write_to_file($error);
        }
    }
}