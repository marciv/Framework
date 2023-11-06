<?php

namespace Framework\Exceptions;

use Exception;

class ControllerNotFoundException extends Exception {
	public function __construct($message = "No controller has been found") {
		parent::__construct($message, "0003");
	}
}
    