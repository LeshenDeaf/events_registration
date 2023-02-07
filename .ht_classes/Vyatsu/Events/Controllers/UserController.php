<?php


namespace Vyatsu\Events\Controllers;

class UserController
{
	private array $values = [];

	/**
	 * UserRegistrator constructor.
	 * @param $fio
	 * @param $email
	 * @param $groups
	 * @throws \Exception
	 */
	public function __construct($fio, $email, $groups)
	{
        global $DB;

        if (!$fio || trim($fio) === '') {
            $fio = bin2hex(random_bytes(8))
                . ' ' . bin2hex(random_bytes(8))
                . ' ' . bin2hex(random_bytes(8));
        }

        $fiioExploded = explode(' ', $fio);
        $password = bin2hex(random_bytes(16));

        $this->addValues([
            'LAST_NAME' => $fiioExploded[0],
            'NAME' => $fiioExploded[1],
            'SECOND_NAME' => $fiioExploded[2],
            'EMAIL' =>  $email,
            'LOGIN' => /*explode('@', */$email/*)[0]*/,
            'PASSWORD' => $password,
            'CONFIRM_PASSWORD' => $password,
            'GROUP_ID' => array_unique([2, ...static::deleteAdminGroupFromArray($groups)]),
            'CHECKWORD_TIME' => md5(\CMain::GetServerUniqID().uniqid()),
            '~CHECKWORD_TIME' => $DB->CurrentTimeFunction(),
            'USER_IP' => $_SERVER["REMOTE_ADDR"],
            'USER_HOST' => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
            'TIME_ZONE' => '',
            'ACTIVE' => 'Y',
            'LID' => SITE_ID,
            'LANGUAGE_ID' => LANGUAGE_ID,
        ]);
	}

    public static function addGroupToUserIfExists(string $email, array $groups, $userId = 0)
    {
        global $USER;

        if (!$userId) {
            $userId = $USER->IsAuthorized()
                ? $USER->GetId()
                : static::userExists($email);
        }
        if (!$userId) {
            return;
        }

        static::addGroupsToUser($userId, $groups);
    }

    public static function userExists(string $email): int
    {
        $res = \CUser::GetList('timestamp_x', 'desc', ['EMAIL' => $email], ['FIELDS' => ['ID']]);

        if ($res->SelectedRowsCount() <= 0) {
            return 0;
        }

        $id = $res->Fetch()['ID'];

        if (!$id) {
            throw new \Exception("Пользователь с email: \"$email\" не найден");
        }

        return $res->Fetch()['ID'];
    }

    public static function addGroupsToUser(int $userId, array $groups)
    {
        \CUser::SetUserGroup($userId, [
			...(\CUser::GetUserGroup($userId) ?? []),
			...static::deleteAdminGroupFromArray($groups)
		]);
    }

	public static function deleteAdminGroupFromArray(array $groups): array
	{
		if (($key = array_search(1, $groups)) !== false) {
			unset($groups[$key]);
		}

		return $groups;
	}

	/**
	 * Adds or sets value to user values array.
	 * @param string $key
	 * @param mixed $value
	 */
	public function addValue(string $key, $value): void
	{
		if ($key === 'GROUP_ID') {
			$value = array_unique([2, ...static::deleteAdminGroupFromArray($value)]);
		}
		$this->values[$key] = $value;
	}

	/**
	 * Adds or sets value to user values array.
	 * @param array $values
	 */
	public function addValues(array $values): void
	{
		if ($values['GROUP_ID']) {
			$values['GROUP_ID'] = array_unique([2, ...static::deleteAdminGroupFromArray($values['GROUP_ID'])]);
		}
		$this->values = array_merge($this->values ?? [], $values);
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values ?? [];
	}

	/**
	 * @param string $key
	 * @return mixed
	 * @throws \Exception
	 */
	public function getValue(string $key)
	{
		if (!isset($values[$key])) {
			throw new \Exception('Ключ не определен');
		}
		return $values[$key];
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	private function createUser(): int
	{
		$user = new \CUser();
		$this->values['SECOND_NAME'] = $this->values['LAST_NAME']
			. ' ' . $this->values['NAME']
			. ' ' . $this->values['SECOND_NAME'];
		$ID = $user->Add($this->values);

		if (intval($ID) <= 0) {
			throw new \Exception($user->LAST_ERROR);
		}

		return $ID;
	}

	/**
	 * @throws \Exception
	 */
	public function register(): int
    {
		global $APPLICATION;

		$events = GetModuleEvents("main", "OnBeforeUserRegister", true);
		foreach($events as $arEvent) {
			if(ExecuteModuleEventEx($arEvent, [&$this->values]) === false) {
				if($err = $APPLICATION->GetException()) {
					throw new \Exception($err->GetString());
				}
			}
		}
		$userId = $this->createUser();

		$this->addValue('USER_ID', $userId);

        return $userId;
	}
}
