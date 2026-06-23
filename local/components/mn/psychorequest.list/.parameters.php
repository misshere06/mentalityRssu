<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "PARAMETERS" => [
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок заявок",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "PAGE_SIZE" => [
            "PARENT" => "BASE",
            "NAME" => "Количество заявок на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
    ],
];