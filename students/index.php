<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Студенты");
?>Студенты
<?php
$APPLICATION->IncludeComponent(
	"mn:users.list", 
	".default", 
	array(
		"GROUPS_IDS" => array(
			0 => "8",
		),
		"USERS_PER_PAGE" => "20",
		"CACHE_TIME" => "3600",
		"COMPONENT_TEMPLATE" => ".default",
		"CACHE_TYPE" => "N"
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>