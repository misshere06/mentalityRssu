<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Highloadblock\HighloadBlockTable;
if (!CModule::IncludeModule('iblock')) {
    die('Модуль инфоблоков не установлен');
}

echo '<pre>';

// 1. Все типы инфоблоков
echo "========== ТИПЫ ИНФОБЛОКОВ ==========\n";
$types = CIBlockType::GetList();
while ($type = $types->Fetch()) {
    echo "{$type['ID']} - {$type['LANG']['ru']['NAME']}\n";
}
echo "\n";

// 2. Все инфоблоки с подробностями
echo "========== ВСЕ ИНФОБЛОКИ ==========\n";
$iblocks = CIBlock::GetList(['ID' => 'ASC'], ['ACTIVE' => 'Y']);
$allIblocks = [];
while ($ib = $iblocks->Fetch()) {
    $ibId = $ib['ID'];
    $allIblocks[$ibId] = [
        'ID' => $ibId,
        'CODE' => $ib['CODE'],
        'NAME' => $ib['NAME'],
        'TYPE' => $ib['IBLOCK_TYPE_ID'],
        'PROPERTIES' => [],
    ];

    echo "ID: {$ibId}, Код: {$ib['CODE']}, Название: {$ib['NAME']}, Тип: {$ib['IBLOCK_TYPE_ID']}\n";
}
echo "\n";

// 3. Детально по каждому инфоблоку с его свойствами
foreach ($allIblocks as $ibId => $ibData) {
    echo "\n========== ИНФОБЛОК: {$ibData['NAME']} (ID={$ibId}) ==========\n";
    echo "Код: {$ibData['CODE']}\n";
    echo "Тип: {$ibData['TYPE']}\n";

    // Свойства
    $props = CIBlockProperty::GetList([], ['IBLOCK_ID' => $ibId]);
    $hasProps = false;
    while ($prop = $props->Fetch()) {
        $hasProps = true;
        $propCode = $prop['CODE'];
        $propType = $prop['PROPERTY_TYPE'];
        $propName = $prop['NAME'];
        $linkIblock = $prop['LINK_IBLOCK_ID'] ?? 'нет';
        $multiple = $prop['MULTIPLE'] == 'Y' ? 'да' : 'нет';

        $allIblocks[$ibId]['PROPERTIES'][$propCode] = [
            'NAME' => $propName,
            'TYPE' => $propType,
            'LINK_IBLOCK_ID' => $prop['LINK_IBLOCK_ID'] ?? null,
            'MULTIPLE' => $prop['MULTIPLE'],
        ];

        echo "  Свойство: {$propCode} ({$propName})\n";
        echo "    Тип: {$propType}\n";
        echo "    Множественное: {$multiple}\n";
        if ($propType == 'E' && $prop['LINK_IBLOCK_ID']) {
            $linkName = $allIblocks[$prop['LINK_IBLOCK_ID']]['NAME'] ?? 'неизвестный';
            echo "    Связано с инфоблоком: {$prop['LINK_IBLOCK_ID']} ({$linkName})\n";
        }
        echo "\n";
    }
    if (!$hasProps) {
        echo "  (нет свойств)\n";
    }
}

// 4. Вывод в виде PHP-массива для копирования в компонент
echo "\n========== PHP-МАССИВ ДЛЯ КОМПОНЕНТА ==========\n";
echo "\$iblockIds = [\n";
foreach ($allIblocks as $id => $data) {
    echo "    '{$data['CODE']}' => {$id}, // {$data['NAME']}\n";
}
echo "];\n\n";

echo "// Массив свойств для каждого инфоблока\n";
echo "\$propertiesCodes = [\n";
foreach ($allIblocks as $ibId => $ibData) {
    echo "    {$ibId} => [ // {$ibData['NAME']}\n";
    foreach ($ibData['PROPERTIES'] as $code => $prop) {
        echo "        '{$code}', // {$prop['NAME']}\n";
    }
    echo "    ],\n";
}
echo "];\n";

echo '</pre>';
echo "\n========== HIGHLOAD-БЛОКИ ==========\n";

if (CModule::IncludeModule('highloadblock')) {


    $hlblocks = HighloadBlockTable::getList()->fetchAll();

    if (empty($hlblocks)) {
        echo "Highload-блоки не найдены\n";
    } else {
        foreach ($hlblocks as $hlblock) {
            echo "ID: {$hlblock['ID']}, Название: {$hlblock['NAME']}, Таблица: {$hlblock['TABLE_NAME']}\n";

            // 👇 Получаем пользовательские поля (UF_*) через CUserTypeEntity
            $userFields = CUserTypeEntity::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                ['ENTITY_ID' => 'HLBLOCK_' . $hlblock['ID']]
            );

            $hasFields = false;
            while ($field = $userFields->Fetch()) {
                $hasFields = true;
                $fieldName = $field['FIELD_NAME'];
                $userType = $field['USER_TYPE_ID'];
                $title = $field['EDIT_FORM_LABEL']['ru'] ?? $field['FIELD_NAME'];
                $multiple = $field['MULTIPLE'] === 'Y' ? 'да' : 'нет';
                $isRequired = $field['MANDATORY'] === 'Y' ? 'да' : 'нет';

                echo "  Поле: {$fieldName}\n";
                echo "    Заголовок: {$title}\n";
                echo "    Тип: {$userType}\n";
                echo "    Множественное: {$multiple}\n";
                echo "    Обязательное: {$isRequired}\n";

                // Дополнительные параметры для некоторых типов
                if (!empty($field['SETTINGS']) && is_array($field['SETTINGS'])) {
                    $settings = array_filter($field['SETTINGS']);
                    if (!empty($settings)) {
                        echo "    Настройки: " . json_encode($settings, JSON_UNESCAPED_UNICODE) . "\n";
                    }
                }
                echo "\n";
            }

            if (!$hasFields) {
                echo "  (нет пользовательских полей)\n";
            }
            echo "\n";
        }
    }
} else {
    echo "Модуль highloadblock не установлен\n";
}
?>