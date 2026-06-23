<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arTemplateParameters = [
    "BOOKING_URL_TEMPLATE" => [
        "PARENT" => "BASE",
        "NAME" => "URL шаблон страницы записи",
        "TYPE" => "STRING",
        "DEFAULT" => "/psycho/booking/#USER_ID#/",
    ],
];