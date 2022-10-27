<?php

namespace GenerCodeClient;

class ApiErrorException extends \Exception {

    protected $errors = [];

    function __construct($url, $status, $body) {
        parent::__construct("API Errors occured with: " . $url, $status);
        $this->errors = json_decode($body, true);
    }

    function getDetails() {
        if (isset($this->errors["error"])) {
            $results = [];
            foreach($this->errors["error"] as $key=>$error) {
                $msg = $key . ": error code - " . $key . " - ";
                if ($key == 1) $msg .= "Value below minimum number required ";
                else if ($key == 2) $msg .= " Value above maximum number required";
                else if ($key == 3) $msg .= " Value must match expected pattern";
                else if ($key == 4) $msg .= " Value contains a pattern that should not be matched";
                else if ($key == 5) $msg .= " Value must be unique";
                else if ($key == 6) $msg .= " Value cannot be null";
                else if ($key == 7) $msg .= " Invalid file extension";
                $results[] = $msg;
            }
            return implode("\n", $results);
        } else {
            return $this->errors;
        }
    } 
}