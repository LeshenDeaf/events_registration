<?php

define('STOP_STATISTICS', true);

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php';

\CModule::IncludeModule("iblock");

$APPLICATION->RestartBuffer();

header('Content-Type: application/json');

function processRequest()
{
    global $USER;

    if ($USER->IsAuthorized() && !$USER->IsAdmin()) {
        return response(
            403,
            ['message' => '<div class="logo"></div><div class="notif_text">Вы уже авторизованы</div>']
        );
    }

    return response(200, [
        'form' => [
            [
                'type' => 'input',
                'label' => 'Логин',
                'name' => 'login',
                'value' => '',
                'is_required' => true,
            ],
            [
                'type' => 'password',
                'label' => 'Пароль',
                'name' => 'password',
                'value' => '',
                'is_required' => true,
            ],
        ],
        'consents' => []
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


