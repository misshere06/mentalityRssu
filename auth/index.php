<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Авторизация");
?>
<?php
$APPLICATION->IncludeComponent(
    "mn:auth.registration",
    ".default",
    array(
        "IBLOCK_CAFEDRA_ID"      => 9,    // ID инфоблока «Кафедры»
        "IBLOCK_SPECIALTY_ID"    => 10,   // ID инфоблока «Специальности»
        "IBLOCK_GROUP_ID"        => 11,   // ID инфоблока «Группы»
        "STUDENT_GROUP_ID"       => 5,    // ID группы пользователей «Студенты» (замените на свой)
        "TEACHER_GROUP_ID"       => 6,    // ID группы «Преподаватели»
        "SOCIAL_WORKER_GROUP_ID" => 8,    // ID группы «Психологи» (соц. работники)
        "REDIRECT_URL"           => "/",  // Куда перенаправлять после входа
    ),
    false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>