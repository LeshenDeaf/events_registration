<?php

namespace Vyatsu\Events\FormFields\Groups;

use \Vyatsu\Events\Interfaces\IStringifiable;
use \Vyatsu\Events\Interfaces\IGroup;
use \Vyatsu\Events\FormFields\Groups\Fields\Radio;

class RadioGroup implements IStringifiable, IGroup
{
	private string $groupName;
	private string $inputName;
	private array $radios;

	public function __construct(
		string $groupName,
		string $inputName,
		array $radios
	) {
		$this->groupName = $groupName . ' *';
		$this->inputName = $inputName;

		$this->setRadios(
			array_column($radios, 'label'),
			array_column($radios, 'value')
		);
	}

	public function getGroupName(): string
	{
		return $this->groupName;
	}

	public function getInputName(): string
	{
		return $this->inputName;
	}

	public function getRadios(): array
	{
		return $this->radios;
	}

	public function setRadios(array $labels, array $values): void
	{
		$this->radios = [];

		if (count($labels) !== count($values)) {
			throw new \RuntimeException('Количество подписей радио кнопок должно раняться количеству значений');
		}

		foreach ($labels as $index => $label) {
			$this->radios[]	= new Radio($label, $this->inputName, $values[$index]);
		}
	}


	public function stringify(): string
	{
		$outStr = "<div class=\"input-block\"><div class=\"input-title\">$this->groupName</div>";

		foreach ($this->radios as $radio) {
			$outStr .= $radio->stringify();
		}

		return "$outStr</div>";
	}

}
