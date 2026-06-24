<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "USER_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID пользователя (по умолчанию текущий)",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
];