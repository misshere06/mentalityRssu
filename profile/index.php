<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Профиль");
?>
<?php
$APPLICATION->IncludeComponent(
        "mn:profile",
        ".default",
        array(
                "USER_ID" => "", // оставьте пустым для текущего пользователя
        ),
        false
); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>