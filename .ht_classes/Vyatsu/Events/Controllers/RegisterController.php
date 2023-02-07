<?php


namespace Vyatsu\Events\Controllers;

use \Vyatsu\Events\Application;
use \Vyatsu\Events\Views;
use Vyatsu\Events\Utils;
use Vyatsu\Events\Utils\FieldChecker;
use \Vyatsu\Events\Application\Description;
use \Vyatsu\Events\Application\Description\AdditionalInfo;

class RegisterController
{
	private int $userId;
	private int $eventId;

	public function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function register(int $eventId): int
	{
		global $USER;

		$this->eventId = $eventId;

		$controller = new EventController($this->eventId);

		$form = $controller->findForm();
		$fields = [];
        $files = [];
		$email = '';
        $fio = '';
        $parentFio = ['exists' => false, 'fio' => ''];
		$age = null;
		$isForeign = null;

		foreach ($form['fields'] as $field) {
			if ($field['type'] === 'text'
				|| $field['type'] === 'header'
			) {
				continue;
			}

			$newField = FieldChecker::{$field['type']}($field);
			$fields[] = $newField;

			if ($newField['name'] === 'email') {
				$email = $newField['value'];
			} elseif ($newField['name'] === 'age' || $newField['name'] === 'birthdate') {
				$age = $newField['name'] === 'birthdate'
					? self::getAge($newField['value'] ?? '')
					: $newField['value'];

				if ($age) {
					ConsentController::isAdult($age);
                    if ($age < $form['min_age']) {
                        throw new \Exception('Ваш возраст меньше минимально допустимого');
                    }
				}
			} elseif ($newField['name'] === 'is_foreign') {
				$isForeign = $newField['value'];
			} elseif ($field['type'] === 'file') {
                $files = $newField['value'];
            } elseif ($newField['name'] === 'fio') {
                $fio = $newField['value'];
            } elseif ($newField['name'] === 'parent_fio') {
                $parentFio = ['exists' => true, 'fio' => $newField['value'] ?? ''];
            }
		}

		if ($controller->canRegister($USER->GetID() ?? 1, $email) !== true) {
			throw new \Exception('Вы уже зарегистрированы');
		}

		$consentController = new ConsentController(
			$age ?? 0, $isForeign ?? false, $form['consents'] ?? [], $parentFio
		);

		$consentController->handleConsents();

        $registerInfo = [
            'email' => $email,
            'fio' => $fio ?? '',
            'groups' => $form['register_after_groups'] ?? [],
            'register_after' => $form['register_after'] ?? false,
        ];

		return $this->save($fields, $email, $controller->isCertRequired(), $files, $registerInfo);
	}

    public static function getAge(string $date): int
    {
        if (!$date) {
            return 0;
        }

        $birthDate = explode('.', $date);

        return (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[0], $birthDate[2]))) > date("md")
            ? ((date("Y") - $birthDate[2]) - 1)
            : (date("Y") - $birthDate[2]));
    }

	public static function isRegistrationStillAvailable(
		int $eventId, int $maxUsers,
		int $userId, string $email = ''
	) {
		$id = static::isAlreadyRegistered($eventId, $userId, $email);

		if ($id === 0) {
			return $maxUsers === 0 || static::getCountOfRegisteredOnEvent($eventId) < $maxUsers;
		}

		return $id;
	}

	public static function isAlreadyRegistered(
		int $eventId,
		int $userId,
		string $email = ''
	): int {
		if ((!$userId || $userId < 2) && !$email) {
			return false;
		}

		$filter = [];

		if ($userId > 1) {
			$filter[] = ['CREATED_BY' => $userId,];
		}
		if ($email) {
			$filter[] = ['PROPERTY_EMAIL' => $email,];
		}

		if (!$filter) {
			throw new \Exception('Не указан email');
		}

		$filter['LOGIC'] = 'OR';

		$res = EventController::generateRes(
			[
				'PROPERTY_EVENT_ID' => $eventId,
				'IBLOCK_ID' => EventController::REGISTER_IBLOCK_ID,
				'ACTIVE' => 'Y',
				$filter
			],
			['IBLOCK_ID', 'ID', 'PROPERTY_EVENT_ID'],
			$arNavParams ?? []
		);

		if ($res->SelectedRowsCount() > 0) {
			return (int)$res->Fetch()['ID'];
		}
		return 0;
	}

	public static function getCountOfRegisteredOnEvent(int $eventId): int
	{
		$res = EventController::generateRes(
			[
				'PROPERTY_EVENT_ID' => $eventId,
				'IBLOCK_ID' => EventController::REGISTER_IBLOCK_ID,
				'ACTIVE' => 'Y',
			],
			['IBLOCK_ID', 'ID', 'PROPERTY_EVENT_ID']
		);

		return $res->SelectedRowsCount() ?? 0;
	}

	/**
	 * @throws \Exception
	 */
	private function save(
        array $fields, string $email, bool $isCertRequired = false, array $files = [], array $registerInfo = []
    ): int {
        global $USER, $DB;

		$el = new \CIBlockElement;

        if ($isCertRequired) {
			$fields[] = [
				'name' => 'has_covid_cert',
				'value' => $USER->IsAuthorized() && $this->doesUserHasCovidCert(),
			];
        }

		$PROP = [
			'EVENT_ID' => $this->eventId,
			'FORM_RESULT_JSON' => json_encode($fields),
			'EMAIL' => $email,
            'FILES' => $files,
		];

		$arLoadProductArray = [
			"MODIFIED_BY" => $this->userId,
			"CREATED_BY" => $this->userId,
			"IBLOCK_SECTION_ID" => false,
			"IBLOCK_ID" => EventController::REGISTER_IBLOCK_ID,
			"PROPERTY_VALUES" => $PROP,
			"NAME" => $email . ' ' . $this->userId . ' ' . $this->eventId,
			"ACTIVE" => "Y",
			"PREVIEW_TEXT" => "",
			"DETAIL_TEXT" => "",
		];

		$DB->StartTransaction();

		try {
			if (!$ID = $el->Add($arLoadProductArray)) {
				throw new \Exception('Не удалось загрузить результаты формы');
			}

			$createdUser = $this->createUserIfNeeded($registerInfo, $email);
			$createdUser['email'] = $email;

			$this->sendEmail($this->eventId, $createdUser, $ID);
			$DB->Commit();
		} catch (\Exception $exception) {
			$DB->Rollback();
			throw $exception;
		} catch(\ParseError $p){
			$DB->Rollback();
			throw $p;
		}
		return (int)$ID;
	}

    private function createUserIfNeeded($registerInfo, $email): array
    {
        global $USER;

        if ($registerInfo['register_after'] && !$USER->IsAuthorized()) {
            if ($userId = UserController::userExists($email)) {
                UserController::addGroupToUserIfExists($email, $registerInfo['groups'], $userId);
                return [];
            }

            $userController = new UserController(
                $registerInfo['fio'], $registerInfo['email'], $registerInfo['groups']
            );

            $userController->register();

			$vals = $userController->getValues();

            return ['login' => $vals['LOGIN'], 'password' => $vals['PASSWORD']];
        }

        if ($registerInfo['groups']) {
            UserController::addGroupToUserIfExists($email, $registerInfo['groups']);
        }

		return [];
    }

	private function sendEmail(int $eventId, array $createdUser, int $recordId)
	{
		$emailController = new EmailController($eventId);

		$emailController->sendEmail($createdUser['email'], $recordId, $createdUser['login'] ?? '', $createdUser['password'] ?? '');
	}

    private function doesUserHasCovidCert(): bool
    {
        global $USER;

        return EventController::generateRes(
			[
				'IBLOCK_ID' => 232,
				'ACTIVE' => 'Y',
				'PROPERTY_IS_EXPIRED' => 'N',
				'PROPERTY_LOGIN' => $USER->GetLogin(),
			],
			[
				'IBLOCK_ID', 'ID', 'NAME', 'ACTIVE',
				'PROPERTY_LOGIN', 'PROPERTY_URL', 'PROPERTY_STATUS'
			]
		)->SelectedRowsCount() > 0;
    }

}
