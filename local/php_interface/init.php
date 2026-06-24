<?php
use Bitrix\Main\EventManager;
use Bitrix\Main\UserTable;


$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('main', 'OnBeforeUserRegister', 'onBeforeUserRegisterHandler');

function onBeforeUserRegisterHandler(&$arFields)
{
    // Если поле UF_ROLE передано и значение не равно "Студент"
    $role = isset($arFields['UF_ROLE']) ? trim($arFields['UF_ROLE']) : '';
    if ($role !== '' && $role !== 'Студент') {
        // Устанавливаем флаг неактивного пользователя
        $arFields['ACTIVE'] = 'N';
        // Сохраняем признак для последующего вывода сообщения (через сессию)
        $_SESSION['REGISTER_NOT_ACTIVE'] = 'Y';
    } else {
        // Студент сразу активен (можно оставить по умолчанию Y)
        $arFields['ACTIVE'] = 'Y';
    }

    // Дополнительно можно отправить уведомление администратору в OnAfterUserAdd
}