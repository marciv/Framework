<?php

namespace Framework\Exceptions;

use Exception;

class InvalidControllerActionFormatException extends Exception
{
    public function __construct($message = "Invalid controller action format", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
    