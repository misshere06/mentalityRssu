<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Студенты");
?>&nbsp;<?$APPLICATION->IncludeComponent(
	"mn:users.list",
	".default",
	Array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"CAFEDRA_IBLOCK_ID" => "9",
		"COMPONENT_TEMPLATE" => ".default",
		"GROUPS_IDS" => [0=>"6",],
		"GROUP_IBLOCK_ID" => "11",
		"ROLE_IBLOCK_ID" => "1",
		"SPECIALTY_IBLOCK_ID" => "10",
		"USERS_PER_PAGE" => "20"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>