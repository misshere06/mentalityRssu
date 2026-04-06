<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_CATEGORIES_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Инфоблок категорий тестов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
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
            "NAME" => "Инфоблок вариантов ответов",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
    ],
];