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
//getEventDates

function processRequest()
{
	$eventController = new \Vyatsu\Events\Controllers\EventController();

	try {
		$res = $eventController->getEventDates();
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

	return response(200, ['message' => "", 'payload' => $res, 'refresh' => false]);
}

function response(int $code, array $data)
{
	http_response_code($code);
	$res = [ 'status' => $code === 200 ? 'success' : 'error' ];

	return json_encode(array_merge($res, $data));
}

echo processRequest();

