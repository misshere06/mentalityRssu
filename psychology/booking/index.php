<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Запись к психологу");
?>

<?$APPLICATION->IncludeComponent(
    "mn:psychorequest.send",
    ".default",
    [
        "IBLOCK_ID" => 12, // ID инфоблока заявок (замените на реальный)
        "PSYCHO_GROUP_ID" => 8, // ID группы "Психологи" (замените)
        "REDIRECT_URL" => "/psychology/requests/",
    ]
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>