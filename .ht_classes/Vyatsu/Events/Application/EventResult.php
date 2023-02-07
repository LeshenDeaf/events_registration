<?php

namespace Vyatsu\Events\Application;

use \Vyatsu\Events\Controllers\EventController;
use \Vyatsu\Events\Utils\FormResult;

class EventResult
{
	private int $id;
	private string $name;

	private array $formFields;
    private bool $isCertRequired;
	private array $results = [];

	public function __construct(
		int $id, string $name, array $formFields, bool $isCertRequired = false
	) {
		$this->id = $id;
		$this->name = $name;
		$this->formFields = $formFields;
        $this->isCertRequired = $isCertRequired;

        if ($this->isCertRequired) {
            $this->formFields[] = ['label' => 'Есть ли сертификат ковид'];
        }
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getFormFields(): array
	{
		return $this->formFields;
	}

	public function getFormResults(): array
	{
		return $this->results;
	}

    public function isCertRequired(): bool
    {
        return $this->isCertRequired;
    }

	/**
	 * Overwrites $this->registeredUsers !!!
	 * @return array<FormResult>
	 */
	public function findFormResults(): array
	{
		$res = \CIBlockElement::GetList(
			["ID" => "DESC"],
			[
				'PROPERTY_EVENT_ID' => $this->id,
				'IBLOCK_ID' => EventController::REGISTER_IBLOCK_ID,
				'ACTIVE' => 'Y',
			],
			false, $arNavParams ?? [],
			[
				'IBLOCK_ID', 'ID', 'CREATED_BY',
				'PROPERTY_EVENT_ID', 'PROPERTY_FORM_RESULT_JSON', 'PROPERTY_FILES'
			]
		);

		$this->registeredUsers = [];
		while ($arFields = $res->Fetch()) {
			$this->registeredUsers[] = new FormResult(
				$arFields['ID'],
				$arFields['CREATED_BY'],
				json_decode(
					$arFields['PROPERTY_FORM_RESULT_JSON_VALUE']['TEXT'],
					true
				) ?? [],
                $arFields['PROPERTY_FILES_VALUE'] ?? []
			);
		}

		return $this->registeredUsers;
	}



}
