<?php

namespace Vyatsu\Events\FormFields;

use \Vyatsu\Events\Interfaces\IStringifiable;

class FormField implements IStringifiable
{
	protected string $label;
	protected string $inputName;
	protected string $type;
	protected string $required;
	private string $val;

	public function __construct(
		string $label,
		string $inputName,
		bool $isRequired = false,
		string $type = 'text',
		string $value = ''
	) {
		$this->label = $isRequired ? "$label *" : $label ;
		$this->inputName = $inputName;
		$this->required = $isRequired ? 'required' : '';
		$this->type = $type;
		$this->val = $value;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getInputName(): string
	{
		return $this->inputName;
	}

	public function getRequired(): string
	{
		return $this->required;
	}

	public function isRequired(): string
	{
		return $this->required;
	}

	public function stringify(): string
	{
		return "<div class=\"input-title\">$this->label</div>"
			. "<input class=\"input-block__field input-block__field_input\" name=\"$this->inputName\" value=\"$this->val\" $this->required>";
	}


}
