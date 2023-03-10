<?php

namespace Vyatsu\Events\RuntimeExceptions;

class AccessException extends \RuntimeException {

	public function __construct(
		$message = "",
	    $code = 0,
	    Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}

}
