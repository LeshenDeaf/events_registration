<?php

namespace Vyatsu\Events\FormFields\Groups\Fields;

use \Vyatsu\Events\FormFields\FormField;
use \Vyatsu\Events\Interfaces\IStringifiable;

class Radio extends FormField implements IStringifiable
{
	private string $value;

	public function __construct(string $label, string $inputName, string $value)
	{
		parent::__construct($label, $inputName);

		$this->value = $value;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function stringify(): string
	{
		return "<label class=\"label\"><input class=\"filter__input radio__input\" name=\"$this->inputName\" type=\"radio\" value=\"$this->value\" required><span class=\"radio__label\">$this->label</span></label><br>";
	}

}
