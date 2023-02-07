<?php

namespace Vyatsu\Events\FormFields;

use \Vyatsu\Events\Interfaces\IStringifiable;

class Select extends FormField implements IStringifiable
{
	private array $labelsValues;

	public function __construct(string $selectLabel, string $inputName, array $options)
	{
		parent::__construct($selectLabel, $inputName);

		$this->setLabelsValues(
			array_column($options, 'label'),
			array_column($options, 'value')
		);
	}

	public function getLabelsValues(): array
	{
		return $this->labelsValues;
	}

	public function setLabelsValues(array $labels, array $values): void
	{
		$this->labelsValues = [];

		if (count($labels) !== count($values)) {
			throw new \Exception('Неверно создано поле select');
		}

		foreach ($labels as $index => $label) {
			$this->labelsValues[$label] = $values[$index];
		}
	}

	public function stringify(): string
	{
		$options = '';

		$selected = 'selected';
		foreach ($this->labelsValues as $label => $value) {
			$options .= '<option value="' . $value . '" id="' . $value . '" ' . $selected . '>'
	                            . "$label</option>";

			$selected = '';
		}

		return '<div class="input-title">'
					. $this->getLabel() . ' *</div>'
                . '<select placeholder="' . $this->getLabel() . '" name="' . $this->getInputName() . '" id="" class="js-select">'
				. $options . '</select>';
	}
}
