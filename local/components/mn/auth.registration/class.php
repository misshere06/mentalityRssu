<?php

namespace Custom\Auth;

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Engine\Response\Component;
use \Bitrix\Main\Error;
use \Bitrix\Main\Errorable;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Result;
use \Bitrix\Main\UserTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class RegistrationComponent extends \CBitrixComponent implements Controllerable, Errorable
{
    protected ErrorCollection $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        return $arParams;
    }

    /**
     * Точка входа компонента
     */
    public function executeComponent()
    {
        global $USER, $APPLICATION;
        Loc::loadMessages(__FILE__);

        // Если пользователь уже авторизован, возможно, редирект
        if ($USER->IsAuthorized()) {
            LocalRedirect('/'); // или другую страницу
        }

        // Определяем режим: авторизация или регистрация (по умолчанию авторизация)
        $this->arResult['MODE'] = $this->request->get('mode') === 'register' ? 'register' : 'login';
        $this->arResult['ERRORS'] = [];
        $this->arResult['SUCCESS'] = false;

        // Подготовка списков для селектов (можно вынести в отдельный метод)
        $this->arResult['ROLES'] = [
            'Студент' => 'Студент',
            'Преподаватель' => 'Преподаватель',
            'Ассистент' => 'Ассистент',
            'Гость' => 'Гость',
        ];

        $this->arResult['GROUPS'] = [
            'ИВТ-21' => 'ИВТ-21',
            'ПМ-22' => 'ПМ-22',
            'ЭК-31' => 'ЭК-31',
        ];

        $this->arResult['SPECIALTIES'] = [
            'Программирование' => 'Программирование',
            'Дизайн' => 'Дизайн',
            'Маркетинг' => 'Маркетинг',
        ];

        // Если была отправка формы
        if ($this->request->isPost() && check_bitrix_sessid()) {
            if ($this->arResult['MODE'] === 'login') {
                $this->processLogin();
            } else {
                $this->processRegister();
            }
        }

        // Подключаем шаблон
        $this->includeComponentTemplate();
    }

    /**
     * Обработка авторизации
     */
    protected function processLogin(): void
    {
        global $USER;
        $login = trim($this->request->getPost('LOGIN'));
        $password = $this->request->getPost('PASSWORD');

        if (empty($login) || empty($password)) {
            $this->arResult['ERRORS'][] = Loc::getMessage('CUSTOM_AUTH_EMPTY_FIELDS');
            return;
        }

        $result = $USER->Login($login, $password, 'Y');
        if ($result === true) {
            // Успешный вход
            LocalRedirect($this->arParams['REDIRECT_URL'] ?: '/');
        } else {
            $this->arResult['ERRORS'][] = $result['MESSAGE'] ?? Loc::getMessage('CUSTOM_AUTH_WRONG_AUTH');
        }
    }

    /**
     * Обработка регистрации
     */
    protected function processRegister(): void
    {
        $fields = [
            'LOGIN' => trim($this->request->getPost('LOGIN')),
            'EMAIL' => trim($this->request->getPost('EMAIL')),
            'PASSWORD' => $this->request->getPost('PASSWORD'),
            'CONFIRM_PASSWORD' => $this->request->getPost('CONFIRM_PASSWORD'),
            'NAME' => trim($this->request->getPost('NAME')),
            'LAST_NAME' => trim($this->request->getPost('LAST_NAME')),
            'SECOND_NAME' => trim($this->request->getPost('SECOND_NAME')),
            'UF_GROUP' => trim($this->request->getPost('UF_GROUP')),
            'UF_SPECIALNOST' => trim($this->request->getPost('UF_SPECIALNOST')),
            'UF_ROLE' => trim($this->request->getPost('UF_ROLE')),
        ];

        // Валидация
        $validationResult = $this->validateRegistrationFields($fields);
        if (!$validationResult->isSuccess()) {
            $this->arResult['ERRORS'] = $validationResult->getErrorMessages();
            return;
        }

        // Определяем активность в зависимости от роли
        $fields['ACTIVE'] = ($fields['UF_ROLE'] === 'Студент') ? 'Y' : 'N';

        // Регистрация пользователя
        $user = new \CUser;
        $userId = $user->Add($fields);
        if (intval($userId) > 0) {
            // Успешная регистрация
            $this->arResult['SUCCESS'] = true;
            $this->arResult['NEED_ACTIVATION'] = ($fields['ACTIVE'] === 'N');

            // Отправка уведомления администратору (опционально)
            if ($fields['ACTIVE'] === 'N') {
                $this->notifyAdminAboutNewUser($userId, $fields);
            }

            // Сохраняем флаг для показа попапа (через сессию или передаём в JS)
            $_SESSION['CUSTOM_REGISTER_SUCCESS'] = [
                'ACTIVE' => $fields['ACTIVE'],
                'MESSAGE' => Loc::getMessage('CUSTOM_AUTH_REGISTER_SUCCESS_NEED_ACTIVATION'),
            ];
        } else {
            $this->arResult['ERRORS'][] = $user->LAST_ERROR;
        }
    }

    /**
     * Валидация полей регистрации
     */
    protected function validateRegistrationFields(array $fields): Result
    {
        $result = new Result();

        if (empty($fields['LOGIN']) || strlen($fields['LOGIN']) < 3) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_LOGIN_MIN_LENGTH')));
        }
        if (empty($fields['EMAIL']) || !check_email($fields['EMAIL'])) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_INVALID_EMAIL')));
        }
        if (empty($fields['PASSWORD']) || strlen($fields['PASSWORD']) < 6) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_PASSWORD_MIN_LENGTH')));
        }
        if ($fields['PASSWORD'] !== $fields['CONFIRM_PASSWORD']) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_PASSWORDS_NOT_MATCH')));
        }
        if (empty($fields['NAME']) || empty($fields['LAST_NAME'])) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_REQUIRED_FIELDS')));
        }
        if (empty($fields['UF_GROUP']) || empty($fields['UF_SPECIALNOST']) || empty($fields['UF_ROLE'])) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_REQUIRED_SELECTS')));
        }

        // Проверка уникальности логина и email
        $userByLogin = UserTable::getRow(['filter' => ['=LOGIN' => $fields['LOGIN']]]);
        if ($userByLogin) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_LOGIN_EXISTS')));
        }
        $userByEmail = UserTable::getRow(['filter' => ['=EMAIL' => $fields['EMAIL']]]);
        if ($userByEmail) {
            $result->addError(new Error(Loc::getMessage('CUSTOM_AUTH_EMAIL_EXISTS')));
        }

        return $result;
    }

    /**
     * Уведомление администратора о новом пользователе, требующем активации
     */
    protected function notifyAdminAboutNewUser(int $userId, array $fields): void
    {
        $adminEmail = \Bitrix\Main\Config\Option::get('main', 'email_from');
        if (!$adminEmail) {
            return;
        }

        $arEventFields = [
            'USER_ID' => $userId,
            'LOGIN' => $fields['LOGIN'],
            'EMAIL' => $fields['EMAIL'],
            'NAME' => $fields['NAME'],
            'LAST_NAME' => $fields['LAST_NAME'],
            'ROLE' => $fields['UF_ROLE'],
            'ACTIVE' => $fields['ACTIVE'],
            'EMAIL_TO' => $adminEmail,
        ];

        \CEvent::Send('CUSTOM_NEW_USER_NEED_ACTIVATION', SITE_ID, $arEventFields);
    }

    /**
     * Реализация интерфейса Errorable
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): ?Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    /**
     * Реализация Controllerable (если планируются AJAX-действия)
     */
    public function configureActions(): array
    {
        return [];
    }
}