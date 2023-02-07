<?php


namespace Vyatsu\Events\Controllers;

use \Vyatsu\Events\Utils\EventGenerator;

class EmailController
{
	public const EMAIL_MESSAGES_IBLOCK_ID = 283;
	public const AR_SELECT = [
		'ID', 'IBLOCK_ID', 'ACTIVE',
		'PROPERTY_EMAIL_TEXT', 'PROPERTY_EVENT_ID',
	];

	private int $eventId = 0;
	private string $eventName = '';
	private string $contacts = '';
	private ?string $emailBody = null;

	public function __construct(int $eventId)
	{
		$this->eventId = $eventId;
		$this->setEventInfo();
	}

	public function getEventId(): int
	{
		return $this->eventId;
	}

	public function getEventName(): string
	{
		return $this->eventName;
	}

	public function getContacts(): string
	{
		return $this->contacts;
	}

	/**
	 * @throws \Exception
	 */
	private function setEventInfo(): void
	{
		$res = EventController::generateRes(
			['IBLOCK_ID' => EventController::EVENTS_IBLOCK_ID , 'ID' => $this->eventId],
			[
				'ID', 'IBLOCK_ID', 'ACTIVE',
				'PROPERTY_CONTACTS', 'NAME'
			]
		);

		if (!($arFields = $res->Fetch())) {
			throw new \Exception("Мероприятие с идентификатором {$this->eventId} на найдено");
		}

		$this->eventName = $arFields['NAME'];
		$this->contacts = implode(', ', EventGenerator::makeContacts(
			$arFields['PROPERTY_CONTACTS_VALUE'] ?? [],
			$arFields['PROPERTY_CONTACTS_DESCRIPTION'] ?? []
		) ?? []);
	}

	public function getEmailBody(): string
	{
		if ($this->emailBody !== null) {
			return $this->emailBody;
		}

		$res = EventController::generateRes(
			['IBLOCK_ID' => static::EMAIL_MESSAGES_IBLOCK_ID , 'PROPERTY_EVENT_ID' => $this->eventId],
			static::AR_SELECT
		);

		if (!($arFields = $res->Fetch())) {
			return '';
		}

		$this->emailBody = $arFields['PROPERTY_EMAIL_TEXT_VALUE']['HTML']
			? html_entity_decode($arFields['PROPERTY_EMAIL_TEXT_VALUE']['HTML'])
			: html_entity_decode($arFields['PROPERTY_EMAIL_TEXT_VALUE']['TEXT'] ?? '');

		return $this->emailBody;
	}

	public function sendEmail(string $email, int $recordId, string $login = '', string $password = ''): void
	{
		\AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("My_BizprocAutoStart","AutoStartAdd"));
		\MessageSend(
			$this->makeTheme(),
			'',
			'',
			$email,
			'',
			[],
			[],
			$this->makeTheme() . '<br>' . $this->makeHeader($recordId, $login, $password) . '<br>' . $this->getEmailBody() . $this->makeFooter()
		);
	}

    public function sendCustomEmail(string $email, string $login = '')
    {
        \MessageSend(
            $this->makeTheme(),
            '',
            '',
            $email,
            '',
            [],
            [],
            $this->makeTheme() . '<br>Уважаемый участник «' . $this->eventName . '». Напоминаем, что для вас открыт доступ для прохождения олимпиады до 24.00 текущего дня. <br>В случае утери логина и пароля напоминаем:<br>'
            . 'Ваш логин:' . $login . '<br> Восстановить доступ вы можете <a href="https://new.vyatsu.ru/account/?forgot_password=yes">https://new.vyatsu.ru/account/?forgot_password=yes</a><br>'
            . $this->makeFooter()
        );
    }

	private function makeTheme(): string
	{
		return "Регистрация на {$this->eventName}";
	}

	private function makeHeader(int $recordId, string $login = '', string $password = ''): string
	{
		$header = "Добрый день! <br> Вы успешно зарегистрировались на <i>{$this->eventName}</i>. <br> Ваш уникальный регистрационный номер - <b>{$recordId}</b>.";

		if ($login && $password) {
			$header .= "<br>Ваш логин и пароль для доступа в <a href=\"https://new.vyatsu.ru/account/\">https://new.vyatsu.ru/account/</a>: </br>Логин - <b>$login</b>,</br>Пароль - <b>$password</b>.";
		}

		return $header;
	}

	private function makeFooter(): string
	{
		$footer = "";

		if ($this->contacts) {
			$footer .= "<br>В случае необходимости, Вы можете связаться с организаторами по: <i>{$this->contacts}</i>";
		}

		return $footer . "<br>Благодарим Вас за интерес, проявленный к мероприятию {$this->eventName}!";
	}






}
