<?php
/**
 * HttpLogger: A simple HTTP request/response logger for API projects.
 * 
 * @copyright Copyright (c) 2019 Aldin Kovačevič <aldin@tribeos.io>
 * @license MIT
 */

namespace HttpLog\HttpModels;

class Error {
    private $error_type;
    private $log_level;
    private $error_code;
    private $description;
    private $file;
    private $line;

    /**
     * Construct the Error object.
     * @param string $error_type Type of the caught error (notice, warning, error, etc.).
     * @param int $log_level Error's log level.
     * @param int $error_code Error code.
     * @param string $description Message description of the caught error.
     * @param string $file The file in which the error occured.
     * @param int $line The line in which the error occured.
     * @return void
     */
    public function __construct($error_type, $log_level, $error_code, $description, $file, $line) {
        $this->error_type = $error_type;
        $this->log_level = $log_level;
        $this->error_code = $error_code;
        $this->description = $description;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * Return the error properties.
     * @return array Array of Error properties.
     */
    public function get_properties() {
        $properties = [ ];
        foreach (get_class_vars(__CLASS__ ) as $property => $value) {
            $properties[$property] = $this->$property;
        }
        /* Return property array */
        return $properties;
    }
}
