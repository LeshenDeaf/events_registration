<?php

namespace Vyatsu\Events\FormFields\Groups;

use \Vyatsu\Events\Interfaces\IStringifiable;
use \Vyatsu\Events\Interfaces\IGroup;
use \Vyatsu\Events\FormFields\Groups\Fields\Checkbox;

class CheckboxGroup implements IStringifiable, IGroup
{
	private string $groupName;
	private string $inputName;
	private array $checkboxes;

	public function __construct(
		string $groupName,
		string $inputName,
		array $checkboxes
	) {
		$this->groupName = $groupName;
		$this->inputName = $inputName;

		$this->setCheckboxes(
			array_column($checkboxes, 'label'),
			array_column($checkboxes, 'value')
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

	public function getCheckboxes(): array
	{
		return $this->radios;
	}

	public function setCheckboxes(array $labels, array $values): void
	{
		$this->checkboxes = [];

		if (count($labels) !== count($values)) {
			throw new \RuntimeException('Количество подписей чекбоксов должно раняться количеству значений');
		}

		foreach ($labels as $index => $label) {
			$this->checkboxes[]	= new Checkbox($label, $this->inputName . '[]', $values[$index]);
		}
	}


	public function stringify(): string
	{
		$outStr = "<div class=\"input-block\"><div class=\"input-title\">$this->groupName</div>";

		foreach ($this->checkboxes as $checkbox) {
			$outStr .= $checkbox->stringify();
		}

		return "$outStr</div>";
	}

}
