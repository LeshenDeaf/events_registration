<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";

\CModule::IncludeModule("iblock");

spl_autoload_register(function ($class_name) {
	if (stripos($class_name, 'bitrix') !== false) {
		return;
	}

	require_once $_SERVER["DOCUMENT_ROOT"]
		. "/events_registration/.ht_classes/"
		. str_replace('\\', '/', $class_name) . '.php';
});


$ec = new \Vyatsu\Events\Controllers\EventController();

$ec->show();

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php";
