<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TESTS_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок тестов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "IBLOCK_QUESTIONS_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок вопросов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "IBLOCK_OPTIONS_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок вариантов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "ELEMENT_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID теста",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "SEF_MODE" => [
            "PARENT" => "BASE",
            "NAME" => "Включить ЧПУ",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "ELEMENT_CODE" => [
            "PARENT" => "BASE",
            "NAME" => "Символьный код теста",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
];