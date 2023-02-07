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

    $eventId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    if (($eventId === 0 || $eventId === '0') && (!$USER->IsAuthorized() || $USER->IsAdmin())) {
        return auth();
    }

	$regController
		= new \Vyatsu\Events\Controllers\RegisterController(
			$USER->GetID() ?? 1
		);

	try {
		$res = $regController->register($eventId);
	} catch (
		\Vyatsu\Events\RuntimeExceptions\FieldCheckerException $fce
	) {
		return response(
			400, [
				'message' => '<div class="logo"></div><div class="notif_text">'
					. $fce->getMessage()
					. '</div>',
				'payload' => $fce->getField(),
                'refresh' => false,
			]
		);
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

	return response(200, ['message' => "Вы зарегистрированы на мероприятие. Ваш номер регистрации: $res", 'payload' => $res, 'refresh' => false,]);
}

function auth() {
    global $APPLICATION, $USER;
    $login =  filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $password =  filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    $USER = new \CUser();

    if (($arAuthResult = $USER->Login($login, $password, 'N', 'Y')) === true) {
        $APPLICATION->arAuthResult = $arAuthResult;
        return response(
            200, [
                'message' => 'Вы успешно авторизованы',
                'payload' => [],
                'refresh' => true,
            ]
        );
    }

    return response(400, [
        'message' => '<div class="logo"></div><div class="notif_text">'
            . 'Неверный логин или пароль'
            . '</div>',
        'payload' => [],
        'refresh' => true,
    ]);
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


