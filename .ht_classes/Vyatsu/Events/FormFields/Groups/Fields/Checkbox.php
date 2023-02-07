<?php

namespace Vyatsu\Events\FormFields\Groups\Fields;

use \Vyatsu\Events\FormFields\FormField;
use \Vyatsu\Events\Interfaces\IStringifiable;

class Checkbox extends FormField implements IStringifiable
{
	private string $value;

	public function __construct(string $label, string $inputName, string $value, bool $isRequired = false)
	{
		parent::__construct($label, $inputName, $isRequired);

		$this->value = $value;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function stringify(): string
	{
		return "<label class=\"checkbox\"><input class=\"filter__input checkbox__input\" name=\"$this->inputName\" type=\"checkbox\" value=\"$this->value\" $this->required><span class=\"checkbox__label\">$this->label</span></label>";
	}

}
