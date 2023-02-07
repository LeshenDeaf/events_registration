<?php

namespace Vyatsu\Events\RuntimeExceptions;

class FieldCheckerException extends \RuntimeException {
	private array $field;

	public function __construct(array $field,
	                            $message = "",
	                            $code = 0,
	                            Throwable $previous = null
	) {
		$this->field = $field;
		parent::__construct($message, $code, $previous);
	}

	public function getField(): array
	{
		return $this->field;
	}

	public function setField(array $field): void
	{
		$this->field = $field;
	}


}
