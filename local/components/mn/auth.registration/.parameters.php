<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "GROUPS" => [
        "BASE" => [
            "NAME" => "Основные настройки",
            "SORT" => 100,
        ],
        "GROUPS_MAP" => [
            "NAME" => "Привязка ролей к группам пользователей",
            "SORT" => 200,
        ],
    ],
    "PARAMETERS" => [
        "IBLOCK_CAFEDRA_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока «Кафедры»",
            "TYPE" => "STRING",
            "DEFAULT" => "9",
        ],
        "IBLOCK_SPECIALTY_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока «Специальности»",
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "IBLOCK_GROUP_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока «Группы»",
            "TYPE" => "STRING",
            "DEFAULT" => "11",
        ],
        "STUDENT_GROUP_ID" => [
            "PARENT" => "GROUPS_MAP",
            "NAME" => "ID группы «Студенты»",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "TEACHER_GROUP_ID" => [
            "PARENT" => "GROUPS_MAP",
            "NAME" => "ID группы «Преподаватели»",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "SOCIAL_WORKER_GROUP_ID" => [
            "PARENT" => "GROUPS_MAP",
            "NAME" => "ID группы «Социальные работники (психологи)»",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "REDIRECT_URL" => [
            "PARENT" => "BASE",
            "NAME" => "URL для редиректа после авторизации",
            "TYPE" => "STRING",
            "DEFAULT" => "/",
        ],
    ],
];