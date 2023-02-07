<?php

define('STOP_STATISTICS', true);

require_once $_SERVER['DOCUMENT_ROOT']
	. '/bitrix/modules/main/include/prolog_before.php';

\CModule::IncludeModule("iblock");

$APPLICATION->RestartBuffer();

header('Content-Type: application/json');

spl_autoload_register(function ($class_name) {
	if (stripos($class_name, 'bitrix') !== false) {
		return;
	}

	require_once $_SERVER["DOCUMENT_ROOT"]
		. "/events_registration/.ht_classes/"
		. str_replace('\\', '/', $class_name) . '.php';
});

function processRequest()
{
	global $USER;

	$page = (int)filter_input(INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT);
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
	$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

	$eventController = new \Vyatsu\Events\Controllers\EventController();

	try {
		$res = $eventController->getEventsLimited($page, $name, $date);
		foreach ($res as $key => $event) {
			$techInfo = $event->getTechInfo();
			$elementId = $techInfo->getElementId();
			$isRegistrationAvailable = $techInfo->isRegistrationAvailable();
			$oldId = $techInfo->getOldId();

			$desc = $event->getDescription();
			$addInfo = $desc->getAdditionalInfo();

			$res[$key] = [
				'id' => $elementId,
				'name' => $desc->getName(),

				'head_unit' => $addInfo->getHeadUnit(),
				'contacts' => $addInfo->getContacts(),

				'desc' => $event->getDescription()->getShortDesc() ?: 'Описание отсутствует 😞',

				'button' => [
					'auth' => !($USER->IsAuthorized() || !$USER->IsAuthorized() && $isRegistrationAvailable),
					'is_shown' => $techInfo->isRegisterNeeded(),
					'classes' => "button-record-event "
						. ($isRegistrationAvailable ? '' : ' button-record-event-disabled')
						. ($techInfo->isMustFillForm() ? ' get_form ' : ' button-register not-fill'),
					'content' => !$isRegistrationAvailable && $oldId !== 1 ? "Ваш номер регистрации: $oldId" : "Записаться",
				],

			];
		}
	} catch (\Exception $e) {
		return response(
			500, [
				'message' => '<div class="logo"></div><div class="notif_text">'
					. $e->getMessage()
					. '</div>',
				'payload' => [],
				'refresh' => false,
			]
		);
	}

	return response(200, ['message' => "", 'payload' => $res, 'refresh' => false, 'request' => compact('page', 'name', 'date')]);
}

function response(int $code, array $data)
{
	http_response_code($code);
	$res = [
		'status' => $code === 200 ? 'success' : 'error'
	];
	return json_encode(array_merge($res, $data));
}

echo processRequest();


