<?php

namespace Vyatsu\Events\FormFields;

use \Vyatsu\Events\Interfaces\IStringifiable;

class Text implements IStringifiable
{
	private string $text;
	public function __construct(string $text)
	{
		$this->text = $text;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function stringify(): string
	{
		return "<p>$this->text</p>";
	}


}
