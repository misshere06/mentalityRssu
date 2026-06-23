<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Студенты");
?>Студенты
<?$APPLICATION->IncludeComponent(
	"mn:users.list",
	"",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "N",
		"GROUPS_IDS" => array("6"),
		"USERS_PER_PAGE" => "20"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>