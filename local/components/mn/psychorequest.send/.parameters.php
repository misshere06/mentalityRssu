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
        "PSYCHO_GROUP_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Группа психологов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "REDIRECT_URL" => [
            "PARENT" => "BASE",
            "NAME" => "URL перенаправления после успешной отправки",
            "TYPE" => "STRING",
            "DEFAULT" => "/psycho/requests/",
        ],
    ],
];