<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Психологи");
?>

<?$APPLICATION->IncludeComponent(
	"mn:users.list", 
	"psycho", 
	[
		"GROUPS_IDS" => [
			0 => "8",
		],
		"USERS_PER_PAGE" => "12",
        "BOOKING_URL_TEMPLATE" => "/psychology/booking/#USER_ID#/",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"COMPONENT_TEMPLATE" => "psycho",
		"CAFEDRA_IBLOCK_ID" => "1",
		"SPECIALTY_IBLOCK_ID" => "1",
		"GROUP_IBLOCK_ID" => "1"
	],
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>