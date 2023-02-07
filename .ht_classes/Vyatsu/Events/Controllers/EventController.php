<?php

namespace Vyatsu\Events\Controllers;

use \Vyatsu\Events\Application;
use \Vyatsu\Events\Views;
use \Vyatsu\Events\Application\{
	Description,
	AdditionalInfo,
	Place
};
use Vyatsu\Events\Utils;
use \Vyatsu\Events\Utils\EventGenerator;

class EventController
{
	public const EVENTS_IBLOCK_ID = 238;
	public const ACCESS_EVENTS_IBLOCK_ID = 239;
	public const REGISTER_IBLOCK_ID = 228;

	public static array $autocompletion = [];

	public static array $arSelect = [
		'IBLOCK_ID', 'ID', 'NAME',
		'PROPERTY_DESCRIPTION', 'PROPERTY_FORM_FIELDS',
		'PROPERTY_TYPE', 'PROPERTY_REGISTER',
		'PROPERTY_PAID', 'PROPERTY_COVID', 'PROPERTY_GROUP_ID',
		'PROPERTY_HEAD_USERS', 'PROPERTY_REGISTER_AFTER',
		'PROPERTY_PHOTOS',
		'PROPERTY_DATES', 'PROPERTY_EVENT_START_DATE', 'PROPERTY_EVENT_END_DATE',

		'PROPERTY_CONSENT_ADULTS', 'PROPERTY_CONSENT_CHILDREN', 'PROPERTY_CONSENT_FOREIGN',

        'PROPERTY_PODR_ORG',

		'PROPERTY_FORMAT', 'PROPERTY_KIND_OF_EDU', 'PROPERTY_MAX_USERS',
		'PROPERTY_SCI_DIR', 'PROPERTY_DOCS', 'PROPERTY_KIND_OF_ACTIVITY',
		'PROPERTY_EDU_DIR', 'PROPERTY_SCI_DIR',
		'PROPERTY_LINK', 'PROPERTY_PLACE', 'PROPERTY_AUDITORY',

        'PROPERTY_CONTACTS', 'PROPERTY_MIN_AGE'
	];

	private int $eventId = 0;

	private EventGenerator $eventGenerator;

    private $user;

	public function __construct(int $elementId = 0)
	{
		if ($elementId !== 0) {
			$this->eventId = $elementId;
		} elseif ($eventId = filter_input(
			INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT
		)) {
            $this->eventId = $eventId;
		}

        if (stripos($_SERVER['REQUEST_URI'], '/events_registration/new/details/') !== false
            && !$this->eventId
        ) {
            \LocalRedirect('/events_registration/new/');
        }

        $this->eventGenerator = new EventGenerator();

        $this->user = \Bitrix\Main\Engine\CurrentUser::get();
	}

	public function index()
	{
//        $user = \Bitrix\Main\Engine\CurrentUser::get();

        $events = $this->getEvents();

        if ($userId = $this->user->getId()) {
            $resController = new ResultsController($userId);
            $moderEventIds = $resController->getAvailableEventIds();
        } else {
            $moderEventIds = [];
        }

        $oldEventsController = new \Vyatsu\Events\Controllers\OldEvents\OldEventsController();
        $oldEvents = $oldEventsController->getOldEvents();

        $view = new Views\EventsList($events, !empty($oldEvents), $moderEventIds ?? []);
		$view->render();

        $oldEventsController->render($oldEvents);
	}

	public function show()
	{
//        $user = \Bitrix\Main\Engine\CurrentUser::get();

        $event = $this->getEvents()[0] ?? [];

		if (!$event) {
			Views\Event::accessDenied();
			return;
		}

        if ($userId = $this->user->getId()) {
            $resController = new ResultsController($userId);
            $moderEventIds = $resController->getAvailableEventIds();
        } else {
            $moderEventIds = [];
        }

		$view = new Views\Event($event, in_array($this->eventId, $moderEventIds));

		$view->render();
	}

	public function findForm(): array
	{
		$res = static::generateRes(
			$this->makeFilter([$this->eventId]),
			[
				'IBLOCK_ID', 'ID', 'NAME', 'PROPERTY_FORM_FIELDS',
				'PROPERTY_CONSENT_ADULTS', 'PROPERTY_CONSENT_CHILDREN', 'PROPERTY_CONSENT_FOREIGN',
                'PROPERTY_MIN_AGE', 'PROPERTY_REGISTER_AFTER', 'PROPERTY_REGISTER_AFTER_GROUPS'
			]
		);

		if (!($arFields = $res->Fetch())) {
			return [];
		}

		$fields = json_decode(
			$arFields['PROPERTY_FORM_FIELDS_VALUE']['TEXT'],
			true
		) ?? [];


		foreach ($fields as $i => $field) {
			if (!$field['name']) {
				continue;
			}
			$newVal = $this->chooseAutocompletion($field['name']);

			if (isset($field['value']) && $newVal) {
				$fields[$i]['value'] = $newVal;
			}
		}

		$consents = [
			'adults' => $arFields['PROPERTY_CONSENT_ADULTS_VALUE'],
			'children' => $arFields['PROPERTY_CONSENT_CHILDREN_VALUE'],
			'foreign' => $arFields['PROPERTY_CONSENT_FOREIGN_VALUE'],
		];

        $min_age = $arFields['PROPERTY_MIN_AGE_VALUE'] ?? 0;

        $register_after = $arFields['PROPERTY_REGISTER_AFTER_VALUE'] === 'Y';
        $register_after_groups = $arFields['PROPERTY_REGISTER_AFTER_GROUPS_VALUE'] ?? [];

		return compact('fields', 'consents', 'min_age', 'register_after', 'register_after_groups');
	}

	public function canRegister(
		int $userId = 1,
		string $email = ''
	) {
		$event = $this->getEventsFiltered()[0] ?? [];

		return RegisterController::isRegistrationStillAvailable(
			$event->getTechInfo()->getElementId(),
			$event->getTechInfo()->getMaxUsers(),
			$userId,
			$email
		);
	}

    public function isCertRequired()
    {
        $event = $this->getEvents()[0] ?? [];

        return $event->getTechInfo()->isCovidCertificateRequired();
    }

	public static function generateRes(array $arFilter, array $arSelect = [], array $arNavParams = [])
	{
		if (empty($arSelect)) {
			$arSelect = static::$arSelect;
		}

        return \CIBlockElement::GetList(
            ["ID" => "DESC"], $arFilter, false, $arNavParams, $arSelect
        );
	}

	private function chooseAutocompletion(string $inputName)
	{
//        $user = \Bitrix\Main\Engine\CurrentUser::get();

		if (static::$autocompletion) {
			return static::$autocompletion[$inputName];
		}

		static::$autocompletion
			= \Vyatsu\Events\User::getUserInfo($this->user->getLogin() ?? '');

		return static::$autocompletion[$inputName];
	}

	/**
	 * @return array<Application\Application>
	 */
	private function getEvents(): array
	{
		return $this->eventGenerator->generate(
			static::generateRes(
				$this->makeAllFilter(),
				static::$arSelect
			),
			static::generateRes(
				$this->makeSubquery(),
				['ID', 'IBLOCK_ID', 'PROPERTY_EVENT_IDS']
			)
		);
	}

	public function getEventsLimited(int $pageNumber = 0, string $name = '', string $date = '')
	{
		return $this->eventGenerator->generate(
			static::generateRes(
				$this->makeAllFilter($name, $date),
				static::$arSelect,
				['nPageSize' => 6, 'iNumPage' => $pageNumber, 'checkOutOfRange' => true]
			),
			static::generateRes(
				$this->makeSubquery(),
				['ID', 'IBLOCK_ID', 'PROPERTY_EVENT_IDS']
			)
		);
	}

	public function getEventDates()
	{
		return $this->eventGenerator->generateDatesArr(
			static::generateRes(
				[
					'IBLOCK_ID' => static::EVENTS_IBLOCK_ID,
					'ACTIVE' => 'Y'
				],
				['IBLOCK_ID', 'ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO'],
				[]
			),
			static::generateRes(
				$this->makeSubquery(),
				['ID', 'IBLOCK_ID', 'PROPERTY_EVENT_IDS']
			)
		);
	}

	private function getEventsFiltered()
	{
		return $this->eventGenerator->generate(
			static::generateRes(
				$this->makeFilterWithSubquery(
					$this->makeSubqueryFilter()
				),
				static::$arSelect
			)
		);
	}

	private function makeAllFilter(string $name = '', string $date = '')
	{
//        $user = \Bitrix\Main\Engine\CurrentUser::get();

		$filter = [
			'IBLOCK_ID' => static::EVENTS_IBLOCK_ID,
			[
				"LOGIC" => "OR",
				[
					'ACTIVE' => 'Y',
					'ACTIVE_DATE' => 'Y'
				],
				['PROPERTY_HEAD_USERS' => $this->user->getId()],
			],
		];

		if ($this->user->isAdmin()) {
			$filter = ['IBLOCK_ID' => static::EVENTS_IBLOCK_ID, 'ACTIVE' => 'Y',];
			if ($name) {
				$filter['NAME'] = "%$name%";
			}
			if ($date) {
				$filter['<=DATE_ACTIVE_FROM'] = $date;
				$filter['>=DATE_ACTIVE_TO'] = $date;
			}
		}

        if ($this->eventId !== 0) {
            $filter['ID'] = $this->eventId;
        }

        return $filter;
	}

	private function makeFilterWithSubquery(array $subqueryFilter): array
	{
//        $user = \Bitrix\Main\Engine\CurrentUser::get();
		return [
			'SUBQUERY' => $subqueryFilter,
			'IBLOCK_ID' => static::EVENTS_IBLOCK_ID,
            [
                "LOGIC" => "OR",
                [
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y'
                ],
                ['PROPERTY_HEAD_USERS' => $this->user->getId()],
            ],
		];
	}

    private function makeSubquery(): array
    {
//        $user = \Bitrix\Main\Engine\CurrentUser::get();

        $userId = $this->user->getId();

        $userInfo = \Vyatsu\Events\User::getUserInfo($login ?? '');

        if ($userId) {
            $userInfo['groups'] = $this->user->getUserGroups();
        } else {
            $userInfo['groups'] = [];
        }

        return $this->makeAccessFilter($userInfo, (bool)$userId);
    }

    private function makeSubqueryFilter(): array
    {
        return [
            'FIELD' => 'PROPERTY_EVENT_IDS',
            'FILTER' => $this->makeSubquery()
        ];
    }

    private function makeFilter(array $ids): array {
		return [
			'ID' => $ids,
			'IBLOCK_ID' => static::EVENTS_IBLOCK_ID,
//			'ACTIVE' => 'Y',
//			'ACTIVE_DATE' => 'Y',
		];
	}

	private function makeAccessFilter(
		array $userArr,
		bool $isAuthorized = false
	): array {
		$arFilter = [
			'IBLOCK_ID' => static::ACCESS_EVENTS_IBLOCK_ID,
			'ACTIVE'    => 'Y',
			'ACTIVE_DATE' => 'Y',

			'PROPERTY_GROUP_IDS' => [...$userArr['groups'], false],
			'PROPERTY_FORM_EDU_NAMES_VALUE' => [$userArr['form_ob'], false],
			'PROPERTY_LEVEL_NAMES_VALUE' => [$userArr['level_name'], false],
			'PROPERTY_FACULTIES' => [$userArr['faculty_short'], false],
			'PROPERTY_GROUP_NAMES' => [$userArr['group_name'], false],
			'PROPERTY_COURSES' => [$userArr['course'], false],
			'PROPERTY_IS_LAST_COURSE_VALUE' => [
				$userArr['is_last_course'] ? 'Y' : false,
				false
			],
		];

		if ($this->eventId) {
			$arFilter['PROPERTY_EVENT_IDS'] = $this->eventId;
		}

		if (!$isAuthorized) {
			$arFilter['PROPERTY_AUTHORIZED_ONLY_VALUE'] = false;
		}

		return $arFilter;
	}



}

