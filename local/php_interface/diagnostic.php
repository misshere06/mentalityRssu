<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

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
?>