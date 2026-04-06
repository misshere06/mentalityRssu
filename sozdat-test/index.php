<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Создать тест");
?><!-- Блок конструктора тестов -->

<?$APPLICATION->IncludeComponent(
        "mn:test.create",
        ".default",
        [
                "IBLOCK_CATEGORIES_ID" => 5,  // ID инфоблока категорий
                "IBLOCK_TESTS_ID" => 6,       // ID инфоблока тестов
                "IBLOCK_QUESTIONS_ID" => 7,   // ID инфоблока вопросов
                "IBLOCK_OPTIONS_ID" => 8,     // ID инфоблока вариантов
        ],
        false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>