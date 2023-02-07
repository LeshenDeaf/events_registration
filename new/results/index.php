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

try {
	$resCon = new \Vyatsu\Events\Controllers\ResultsController($USER->GetID() ?? 0);

	$resCon->control();
} catch (\RuntimeException $re) {
	echo "<div class=\"event-title\">{$re->getMessage()}</div>";
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php";
