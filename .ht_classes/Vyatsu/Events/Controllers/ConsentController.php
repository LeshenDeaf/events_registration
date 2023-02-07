<?php


namespace Vyatsu\Events\Controllers;

class ConsentController
{
	private int $age;
	private bool $isForeign;
	private array $formConsents;
    private array $parentFio;

	public function __construct(
        int $age,
        bool $isForeign,
        array $formConsents,
        array $parentFio = ['exists' => false, 'fio' => '']
    ) {
		$this->age = $age;
		$this->isForeign = $isForeign;
		$this->formConsents = $formConsents;
        $this->parentFio = $parentFio;
	}

	/**
	 * @param int $age
	 * @return bool isAdult
	 * @throws \Exception
	 */
	public static function isAdult(int $age = 0): bool
	{
		if (!$age) {
			throw new \Exception('Не указан возраст');
		}

		if ($age < 0 || $age > 130) {
			throw new \Exception('Указан недопустимый возраст');
		}

		return $age > 17;
	}

	/**
	 * Checks if arrays $a and $b are equal (has same size and values regardless of order)
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	public static function areEqual(array $a, array $b): bool {
		return count($a) == count($b) && !array_diff($a, $b);
	}

	public function handleConsents()
	{
		$consents = $this->checkAndGetConsents();

		if (!$consents) {
			throw new \Exception('Отсутствуют согласия на обработку ПД');
		}

		if (!is_array($consents)) {
			return;
		}

		if (!$this->areEqual(
				$consents['consents'],
				$this->formConsents[$consents['type']]
		)) {
			throw new \Exception('Даны не все согласия на обработку ПД');
		}

		$this->agreeWithConsents($consents['consents']);
	}

	/**
	 * @param array<int|string> $consents
	 * @return void
	 */
	public function agreeWithConsents(array $consents)
	{
		global $USER;

		foreach ($consents as $consent) {
			\Bitrix\Main\UserConsent\Consent::addByContext(
				$consent, null, null, ['USER_ID' => $USER->GetID() ?? 0]
			);
		}
	}

    /**
     * @return bool|array["type" => string, "consents" => array]
     * @throws \Exception
     */
	private function checkAndGetConsents() {
		$reformatConsents =
			fn ($type) => [
				'type' => $type,
				'consents' => array_intersect(
					filter_input(
						INPUT_POST, 'consents',
						FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY
					),
					$this->formConsents[$type]
				)
			];

		if ($this->isNoConcepts()) {
			return true;
		}

		if ($this->isOnlyAdults()) {
			return $reformatConsents('adults');
		}

		if ($this->isOnlyChildren()) {
            if ($this->parentFio['exists'] && !$this->parentFio['fio']) {
                throw new \Exception('Не заполнено ФИО законного представителя');
            }
			return $reformatConsents('children');
		}

		if ($this->isForeignConcepts()) {
			return $reformatConsents('foreign');
		}

		if ($this->hasAdultsAndChildren()) {
			if (static::isAdult($this->age)) {
				return $reformatConsents('adults');
			}

            if ($this->parentFio['exists'] && !$this->parentFio['fio']) {
                throw new \Exception('Не заполнено ФИО законного представителя');
            }

			return $reformatConsents('children');
		}

		return $reformatConsents('adults');
	}

	private function isNoConcepts(): bool
	{
		return !$this->formConsents
			|| !$this->formConsents['adults']
			&& !$this->formConsents['children']
			&& !$this->formConsents['foreign'];
	}

	private function isOnlyAdults(): bool
	{
		return $this->formConsents['adults']
			&& !$this->formConsents['children']
			&& !$this->formConsents['foreign'];
	}

	private function isOnlyChildren(): bool
	{
		return !$this->formConsents['adults']
			&& $this->formConsents['children']
			&& !$this->formConsents['foreign'];
	}

	private function isForeignConcepts(): bool
	{
		return $this->isForeign && $this->formConsents['foreign'];
	}

	private function hasAdultsAndChildren(): bool
	{
		return $this->formConsents['adults']
			&& $this->formConsents['children'];
	}

}
