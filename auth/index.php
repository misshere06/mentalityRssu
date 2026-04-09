<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Авторизация");
?><?php
$APPLICATION->IncludeComponent(
    "mn:auth.registration",
    "",
    [
        "REDIRECT_URL" => "/" // Куда перенаправлять после успешного входа
    ],
    false
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>