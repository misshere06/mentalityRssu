<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Студенты");
?>Студенты&nbsp;<?$APPLICATION->IncludeComponent(
	"mn:users.list", 
	".default", 
	[
		"COMPONENT_TEMPLATE" => ".default",
		"GROUPS_IDS" => [
			0 => "6",
		],
		"ROLE_IBLOCK_ID" => "1",
		"SPECIALTY_IBLOCK_ID" => "10",
		"USERS_PER_PAGE" => "20",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CAFEDRA_IBLOCK_ID" => "9",
		"GROUP_IBLOCK_ID" => "11"
	],
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>