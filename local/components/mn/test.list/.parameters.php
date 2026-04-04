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
        "CATEGORY_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID категории (0 - все)",
            "TYPE" => "STRING",
            "DEFAULT" => "0",
        ],
        "PAGE_SIZE" => [
            "PARENT" => "BASE",
            "NAME" => "Количество тестов на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "SEF_MODE" => [
            "PARENT" => "BASE",
            "NAME" => "Включить ЧПУ",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "DETAIL_URL" => [
            "PARENT" => "BASE",
            "NAME" => "Шаблон URL детальной страницы",
            "TYPE" => "STRING",
            "DEFAULT" => "/test/detail/#ELEMENT_ID#/",
        ],
    ],
];