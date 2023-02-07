<?php

namespace Vyatsu\Events\Utils;

use \Vyatsu\Events\Application;
use \Vyatsu\Events\Application\Description;
use \Vyatsu\Events\Application\Description\AdditionalInfo;
use \Vyatsu\Events\Application\Description\Place;
use \Vyatsu\Events\Controllers\RegisterController;

class EventGenerator
{
	public function __construct() {}

	/**
	 * @param \CDBResult $result
	 * @param \CDBResult|null $availableToRegister
	 * @return array
	 */
	public function generate(\CDBResult $result, \CDBResult $availableToRegister = null): array
	{
        $events = [];

		if ($result->SelectedRowsCount() == 0) {
			return $events;
		}

		$availableToRegisterIds = [];

		if ($availableToRegister) {
			while ($arFields = $availableToRegister->Fetch()) {
				$availableToRegisterIds = array_merge($arFields['PROPERTY_EVENT_IDS_VALUE'], $availableToRegisterIds);
			}
		}

		while ($arFields = $result->Fetch()) {
			$events[] = $this->makeEvent($arFields, in_array($arFields['ID'], $availableToRegisterIds));
		}

        return $events;
	}

	public function generateDatesArr(\CDBResult $result): array
	{
		$events = [];

		if ($result->SelectedRowsCount() == 0) {
			return $events;
		}

		while ($arFields = $result->Fetch()) {
			$date = explode('.', explode(' ', $arFields['DATE_ACTIVE_FROM'])[0]);
			$events[] = ['id' => $arFields['ID'], 'day' => +$date[0], 'month' => $date[1] - 1, 'year' => +$date[2]];
		}

		return $events;
	}

	public function makeEvent(array $arFields, bool $hasAccess): Application\Application
	{
		return new Application\Application(
			new Description\Description(
				$arFields['NAME'],
				$arFields['PROPERTY_DESCRIPTION_VALUE']['TEXT'] ?? '',
				$this->generateAdditionalInfo($arFields)
			),
			new Application\Form(
				json_decode(
					$arFields['PROPERTY_FORM_FIELDS_VALUE']['TEXT'],
					true
				) ?? []
			),
			$this->generateTechInfo($arFields, $hasAccess)
		);
	}

	private function generateAdditionalInfo(array $arFields): AdditionalInfo
	{
		return new AdditionalInfo(
			Photo::makePhotosArray(
				$arFields['PROPERTY_PHOTOS_VALUE'] ?? []
			),
			Document::makeDocumentsArray(
				$arFields['PROPERTY_DOCS_VALUE'] ?? []
			),
			new Place(
				$arFields['PROPERTY_PLACE_VALUE'] ?? [],
				$arFields['PROPERTY_LINK_VALUE'] ?? [],
				$arFields['PROPERTY_AUDITORY_VALUE'] ?? [],
			),
			AdditionalInfo::makeDatesArr(
				$arFields['PROPERTY_DATES_VALUE'] ?? [],
				$arFields['PROPERTY_DATES_DESCRIPTION'] ?? []
			),
			\Vyatsu\Events\Moderator::makeModeratorsArr(
				$arFields['PROPERTY_HEAD_USERS_VALUE'] ?? []
			),
			(float)$arFields['PROPERTY_PAID_VALUE'] ?? 0,
			[
				'science' => $arFields['PROPERTY_SCI_DIR_VALUE'] ?? '',
				'education' => $arFields['PROPERTY_EDU_DIR_VALUE'] ?? ''
			],
            $arFields['PROPERTY_PODR_ORG_VALUE'] ?? '',
           	static::makeContacts(
                $arFields['PROPERTY_CONTACTS_VALUE'] ?? [],
                $arFields['PROPERTY_CONTACTS_DESCRIPTION'] ?? []
            )
        );
	}

    public static function makeContacts(array $contacts, array $descriptions): array
    {
        return array_map(static fn ($c, $d) => $c . ' — ' . $d, $contacts, $descriptions);
    }

	private function generateTechInfo(array $arFields, bool $hasAccess): Application\TechInfo
	{
		global $USER;

		$canRegister = RegisterController::isRegistrationStillAvailable(
			$arFields['ID'],
			$arFields['PROPERTY_MAX_USERS_VALUE'] ?? 0,
			$USER->GetId() ?? 1,
			$USER->IsAuthorized()
		);

		return new Application\TechInfo(
			$arFields['ID'],
			$arFields['PROPERTY_TYPE_VALUE'] ?? '',
			$arFields['PROPERTY_FORMAT_VALUE'] === 'online',
			$arFields['PROPERTY_REGISTER_VALUE'] === 'Y',
			$arFields['PROPERTY_MAX_USERS_VALUE'] ?? 0,
			RegisterController::getCountOfRegisteredOnEvent($arFields['ID']),
            $hasAccess && $canRegister === true,
			json_decode(
				$arFields['PROPERTY_FORM_FIELDS_VALUE']['TEXT'],
				true
			) ?? [],
			$arFields['PROPERTY_COVID_VALUE'] === 'Y',
			[
				'adults' => $arFields['PROPERTY_CONSENT_ADULTS_VALUE'] ?? [],
				'children' => $arFields['PROPERTY_CONSENT_CHILDREN_VALUE'] ?? [],
				'foreign' => $arFields['PROPERTY_CONSENT_FOREIGN_VALUE'] ?? [],
			],
            $arFields['PROPERTY_MIN_AGE_VALUE'] ?? 0,
			$canRegister !== false ? $canRegister : 0
		);
	}

}
