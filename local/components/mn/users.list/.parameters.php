<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\GroupTable;

$arGroups = [];
$rsGroups = GroupTable::getList([
    'select' => ['ID', 'NAME'],
    'order' => ['C_SORT' => 'ASC']
]);
while ($group = $rsGroups->fetch()) {
    $arGroups[$group['ID']] = "[{$group['ID']}] {$group['NAME']}";
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
        "USERS_PER_PAGE" => [
            "PARENT" => "BASE",
            "NAME" => "Количество пользователей на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ],
        "CACHE_TIME" => ["DEFAULT" => 3600],
    ]
];