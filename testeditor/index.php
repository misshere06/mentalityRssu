<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("testeditor");
?><pre><br></pre> <br>

<?php
$APPLICATION->IncludeComponent(
        'mn:test.redact',
        '.default',
        [],
        false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>