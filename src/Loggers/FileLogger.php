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
    /** @var string Path to log file. */
    private $log_file;

    /**
     * Construct the file logger.
     * @param string File log path.
     * @return void
     */
    public function __construct($path) {
        parent::__construct();
        $this->log_file = $path;
    }

    /**
     * Log the incoming request/response pair.
     * Logs the incoming request and corresponding response into a file.
     * @return void
     */
    public function log() {
        $log = array_merge($this->request->get_properties(), $this->response->get_properties());
        $log = $this->format_output($log);
        try {
            file_put_contents($this->log_file, $log."\n", FILE_APPEND);
        } catch (\Throwable $e) {
            $e->getTraceAsString();
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
}