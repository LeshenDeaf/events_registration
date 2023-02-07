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

	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

	if (!$id) {
		return response(
			403,
			['message' => '<div class="logo"></div><div class="notif_text">Не указан id элемента</div>']
		);
	}

	$controller = new \Vyatsu\Events\Controllers\EventController($id);

	try {
		if (!$controller->canRegister(
			$USER->GetID() ?? 1,
			filter_input(INPUT_POST, 'email', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''
		)
		) {
			return response(
				403, [
					'message' => '<div class="logo"></div><div class="notif_text">Регистрация на мероприятие закрыта</div>',
					'form' => []
				]
			);
		}
	} catch (\Error $e) {
		return response(
			403, [
				'message' => '<div class="logo"></div><div class="notif_text">Регистрация на мероприятие закрыта.</div>',
				'form' => [],
			]
		);
	}
	$form = $controller->findForm();

	if (!$form) {
		return response(
			404, [
				'message' => '<div class="logo"></div><div class="notif_text">Для этого мероприятия форма отсутствует</div>',
				'form' => $form
			]
		);
	}

	foreach ($form['consents'] as $age => $consents) {
		foreach ($consents as $i => $consent) {
			$agreement = new \Bitrix\Main\UserConsent\Agreement($consent);

			$form['consents'][$age][$i] = [
				'id' => $agreement->getId(),
				'label' => $agreement->getLabelText(),
                'text' => $agreement->getData()['AGREEMENT_TEXT'],
			];
		}
	}

	return response(200, [
		'form' => $form['fields'],
		'consents' => $form['consents'],
        'min_age' => $form['min_age'] ?? 0,
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


