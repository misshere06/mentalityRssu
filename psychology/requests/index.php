<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Психологи");
?>

<?$APPLICATION->IncludeComponent(
	"mn:psychorequest.list", 
	".default", 
	[
		"IBLOCK_ID" => "12",
		"PAGE_SIZE" => "10",
		"COMPONENT_TEMPLATE" => ".default"
	],
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>