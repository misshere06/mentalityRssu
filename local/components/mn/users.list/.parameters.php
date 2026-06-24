<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\GroupTable;

Loader::includeModule('iblock');
Loader::includeModule('main');

// Группы пользователей
$arGroups = [];
$rsGroups = GroupTable::getList([
    'select' => ['ID', 'NAME'],
    'order' => ['C_SORT' => 'ASC']
]);
while ($group = $rsGroups->fetch()) {
    $arGroups[$group['ID']] = "[{$group['ID']}] {$group['NAME']}";
}

// Инфоблоки
$arIblocks = [];
$rsIblocks = CIBlock::GetList(['NAME' => 'ASC'], ['CHECK_PERMISSIONS' => 'N']);
while ($ib = $rsIblocks->Fetch()) {
    $arIblocks[$ib['ID']] = "[{$ib['ID']}] {$ib['NAME']}";
}

$arComponentParameters = [
    "PARAMETERS" => [
        "GROUPS_IDS" => [
            "PARENT" => "BASE",
            "NAME" => "Группы пользователей",
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arGroups,
            "SIZE" => 8,
        ],
        "CAFEDRA_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок кафедр",
            "TYPE" => "LIST",
            "VALUES" => $arIblocks,
            "DEFAULT" => "",
        ],
        "SPECIALTY_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок специальностей",
            "TYPE" => "LIST",
            "VALUES" => $arIblocks,
            "DEFAULT" => "",
        ],
        "GROUP_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок учебных групп",
            "TYPE" => "LIST",
            "VALUES" => $arIblocks,
            "DEFAULT" => "",
        ],
        "USERS_PER_PAGE" => [
            "PARENT" => "BASE",
            "NAME" => "Количество пользователей на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ],
        "CACHE_TIME" => ["DEFAULT" => 3600],
    ]
];