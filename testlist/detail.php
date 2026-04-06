<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Психологический тест");

// Получаем символьный код из URL (при ЧПУ)
$code = trim($_REQUEST['CODE'] ?? '');
if (empty($code)) {
    // Если код не передан, пробуем взять из ELEMENT_ID
    $id = (int)($_REQUEST['ELEMENT_ID'] ?? 0);
    if ($id > 0) {
        $res = CIBlockElement::GetList([], ['IBLOCK_ID'=>6, 'ID'=>$id], false, false, ['CODE']);
        if ($el = $res->Fetch()) $code = $el['CODE'];
    }
}
if (empty($code)) {
    LocalRedirect('/testlist/');
    return;
}
?>

<?$APPLICATION->IncludeComponent(
    "mn:test.detail",
    "onequestion",
    [
        "IBLOCK_TESTS_ID" => 6,
        "IBLOCK_QUESTIONS_ID" => 7,
        "IBLOCK_OPTIONS_ID" => 8,
        "ELEMENT_CODE" => $code,
    ],
    null,
    ['TEMPLATE' => 'onequestion']
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>