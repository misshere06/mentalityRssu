<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!CModule::IncludeModule('iblock')) die('Модуль инфоблоков не загружен');

$testsIblockId = 6;       // ID вашего инфоблока "Психологические тесты"
$categoriesIblockId = 5;  // ID инфоблока "Категории тестов"

$propRes = CIBlockProperty::GetList([], ['IBLOCK_ID' => $testsIblockId, 'CODE' => 'CATEGORY']);
if (!$propRes->Fetch()) {
    $prop = new CIBlockProperty;
    $id = $prop->Add([
        'IBLOCK_ID' => $testsIblockId,
        'NAME' => 'Категория',
        'CODE' => 'CATEGORY',
        'PROPERTY_TYPE' => 'E',
        'LINK_IBLOCK_ID' => $categoriesIblockId,
        'ACTIVE' => 'Y',
        'SORT' => 100,
    ]);
    if ($id) {
        echo "Свойство CATEGORY успешно добавлено, ID={$id}";
    } else {
        echo "Ошибка: " . $prop->LAST_ERROR;
    }
} else {
    echo "Свойство CATEGORY уже существует";
}
?>