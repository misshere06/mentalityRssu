<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список тестов");
?>
<?$APPLICATION->IncludeComponent(
    "mn:test.list",
    "cards",
    [
        "IBLOCK_TESTS_ID" => 6,
        "CATEGORY_ID" => $_REQUEST['CATEGORY_ID'] ?? 0,
        "PAGE_SIZE" => 12,
        "DETAIL_URL" => "/tests/#ELEMENT_CODE#/",   // ЧПУ ссылка на детальную
    ],
    false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>