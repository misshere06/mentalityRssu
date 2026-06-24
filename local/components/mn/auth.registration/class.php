<?php

namespace Custom\Auth;

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Error;
use \Bitrix\Main\Errorable;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Result;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\FileTable;
use \CUser;
use \CFile;
use \CIBlockElement;
use \CUserFieldEnum;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class RegistrationComponent extends \CBitrixComponent implements Controllerable, Errorable
{
    protected ErrorCollection $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        // Приводим ID инфоблоков и групп к целым числам
        $arParams['IBLOCK_CAFEDRA_ID'] = (int)$arParams['IBLOCK_CAFEDRA_ID'];
        $arParams['IBLOCK_SPECIALTY_ID'] = (int)$arParams['IBLOCK_SPECIALTY_ID'];
        $arParams['IBLOCK_GROUP_ID'] = (int)$arParams['IBLOCK_GROUP_ID'];
        $arParams['STUDENT_GROUP_ID'] = (int)$arParams['STUDENT_GROUP_ID'];
        $arParams['TEACHER_GROUP_ID'] = (int)$arParams['TEACHER_GROUP_ID'];
        $arParams['SOCIAL_WORKER_GROUP_ID'] = (int)$arParams['SOCIAL_WORKER_GROUP_ID'];
        return $arParams;
    }

    public function executeComponent()
    {
        global $USER, $APPLICATION;
        Loc::loadMessages(__FILE__);

        if ($USER->IsAuthorized()) {
            LocalRedirect($this->arParams['REDIRECT_URL'] ?: '/');
        }

        // Режим: login или register
        $this->arResult['MODE'] = $this->request->get('mode') === 'register' ? 'register' : 'login';
        $this->arResult['ERRORS'] = [];
        $this->arResult['SUCCESS'] = false;

        // Загружаем модуль iblock, если нужен режим регистрации
        if ($this->arResult['MODE'] === 'register') {
            if (!Loader::includeModule('iblock')) {
                $this->arResult['ERRORS'][] = 'Модуль информационных блоков не установлен.';
                $this->includeComponentTemplate();
                return;
            }

            $this->arResult['CAFEDRAS'] = $this->getIblockElements($this->arParams['IBLOCK_CAFEDRA_ID']);
            $this->arResult['SPECIALTIES'] = $this->getIblockElements($this->arParams['IBLOCK_SPECIALTY_ID']);
            $this->arResult['GROUPS'] = $this->getIblockElements($this->arParams['IBLOCK_GROUP_ID']);
            $this->arResult['ROLES'] = $this->getUfRoleEnum();
        }

        // Обработка формы
        if ($this->request->isPost() && check_bitrix_sessid()) {
            if ($this->arResult['MODE'] === 'login') {
                $this->processLogin();
            } else {
                $this->processRegister();
            }
        }

        $this->includeComponentTemplate();
    }

    /**
     * Авторизация (без изменений в логике)
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
            LocalRedirect($this->arParams['REDIRECT_URL'] ?: '/');
        } else {
            $this->arResult['ERRORS'][] = $result['MESSAGE'] ?? Loc::getMessage('CUSTOM_AUTH_WRONG_AUTH');
        }
    }

    /**
     * Регистрация (полностью переработана)
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
            'UF_CAFEDRA' => (int)$this->request->getPost('UF_CAFEDRA'),
            'UF_SPECIALNOST' => (int)$this->request->getPost('UF_SPECIALNOST'),
            'UF_GROUP' => (int)$this->request->getPost('UF_GROUP'),
            'UF_ROLE' => trim($this->request->getPost('UF_ROLE')),
        ];

        // Загрузка фото
        $photoId = null;
        if (!empty($_FILES['PROFILE_PHOTO']) && $_FILES['PROFILE_PHOTO']['error'] == 0) {
            $photoId = $this->uploadProfilePhoto();
            if (!$photoId) {
                $this->arResult['ERRORS'][] = 'Ошибка загрузки фото. Допустимы JPG, PNG размером до 5 МБ.';
                return;
            }
        }

        // Валидация
        $validationResult = $this->validateRegistrationFields($fields);
        if (!$validationResult->isSuccess()) {
            $this->arResult['ERRORS'] = $validationResult->getErrorMessages();
            return;
        }

        // Все пользователи неактивны до проверки администратором
        $fields['ACTIVE'] = 'N';
        $fields['PERSONAL_PHOTO'] = $photoId; // ID загруженного файла

        // Определяем ID группы Bitrix по роли
        $groupMap = [
            'Студент' => $this->arParams['STUDENT_GROUP_ID'],
            'Преподаватель' => $this->arParams['TEACHER_GROUP_ID'],
            'Психолог' => $this->arParams['SOCIAL_WORKER_GROUP_ID'], // Соц.работник
        ];
        $userGroupId = $groupMap[$fields['UF_ROLE']] ?? 0;

        // Регистрация
        $user = new CUser;
        // Передаём группу в параметрах, если задана
        $arUserFields = $fields;
        if ($userGroupId > 0) {
            $arUserFields['GROUP_ID'] = [$userGroupId];
        }

        $userId = $user->Add($arUserFields);
        if (intval($userId) > 0) {
            $this->arResult['SUCCESS'] = true;
            $this->arResult['SUCCESS_MESSAGE'] = 'Регистрация прошла успешно. Ваша учётная запись будет активирована администратором после проверки данных.';
            // Уведомление администратору
            $this->notifyAdminAboutNewUser($userId, $fields);
        } else {
            $this->arResult['ERRORS'][] = $user->LAST_ERROR;
        }
    }

    /**
     * Загрузка фото профиля
     * @return int|false ID файла или false при ошибке
     */
    protected function uploadProfilePhoto()
    {
        $file = $_FILES['PROFILE_PHOTO'];
        $arFile = CFile::MakeFileArray($file['tmp_name']);
        if (!is_array($arFile)) {
            return false;
        }
        $arFile['name'] = $file['name']; // оригинальное имя
        // Проверка расширения и размера
        $maxSize = 5 * 1024 * 1024; // 5 МБ
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions) || $file['size'] > $maxSize) {
            return false;
        }
        $fid = CFile::SaveFile($arFile, 'main');
        return $fid ?: false;
    }

    /**
     * Валидация всех полей
     */
    protected function validateRegistrationFields(array $fields): Result
    {
        $result = new Result();

        // Базовые проверки
        if (empty($fields['LOGIN']) || strlen($fields['LOGIN']) < 3) {
            $result->addError(new Error('Логин должен быть не менее 3 символов.'));
        }
        if (empty($fields['EMAIL']) || !check_email($fields['EMAIL'])) {
            $result->addError(new Error('Некорректный email.'));
        }
        if (empty($fields['PASSWORD']) || strlen($fields['PASSWORD']) < 6) {
            $result->addError(new Error('Пароль должен содержать не менее 6 символов.'));
        }
        if ($fields['PASSWORD'] !== $fields['CONFIRM_PASSWORD']) {
            $result->addError(new Error('Пароли не совпадают.'));
        }
        if (empty($fields['NAME']) || empty($fields['LAST_NAME'])) {
            $result->addError(new Error('Имя и фамилия обязательны.'));
        }

        // Проверка выбранных справочников (существование ID)
        if ($fields['UF_CAFEDRA'] <= 0 || !$this->isIblockElementExists($this->arParams['IBLOCK_CAFEDRA_ID'], $fields['UF_CAFEDRA'])) {
            $result->addError(new Error('Выберите кафедру из списка.'));
        }
        if ($fields['UF_SPECIALNOST'] <= 0 || !$this->isIblockElementExists($this->arParams['IBLOCK_SPECIALTY_ID'], $fields['UF_SPECIALNOST'])) {
            $result->addError(new Error('Выберите специальность из списка.'));
        }
        if ($fields['UF_GROUP'] <= 0 || !$this->isIblockElementExists($this->arParams['IBLOCK_GROUP_ID'], $fields['UF_GROUP'])) {
            $result->addError(new Error('Выберите группу из списка.'));
        }

        // Проверка допустимой роли
        $allowedRoles = array_keys($this->getUfRoleEnum());
        if (!in_array($fields['UF_ROLE'], $allowedRoles)) {
            $result->addError(new Error('Выберите роль из списка.'));
        }

        // Уникальность логина и email
        if (UserTable::getRow(['filter' => ['=LOGIN' => $fields['LOGIN']]])) {
            $result->addError(new Error('Пользователь с таким логином уже существует.'));
        }
        if (UserTable::getRow(['filter' => ['=EMAIL' => $fields['EMAIL']]])) {
            $result->addError(new Error('Пользователь с таким email уже зарегистрирован.'));
        }

        return $result;
    }

    /**
     * Получить элементы инфоблока в виде [ID => NAME]
     */
    protected function getIblockElements(int $iblockId): array
    {
        if ($iblockId <= 0) {
            return [];
        }
        $items = [];
        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'NAME' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );
        while ($el = $res->Fetch()) {
            $items[$el['ID']] = $el['NAME'];
        }
        return $items;
    }

    /**
     * Проверка существования элемента инфоблока
     */
    protected function isIblockElementExists(int $iblockId, int $elementId): bool
    {
        if ($iblockId <= 0 || $elementId <= 0) {
            return false;
        }
        return (bool)CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $elementId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID']
        )->Fetch();
    }

    /**
     * Получить список значений пользовательского поля UF_ROLE
     * @return array ['Студент' => 'Студент', ...]
     */
    protected function getUfRoleEnum(): array
    {
        $roles = [];
        $rs = CUserFieldEnum::GetList([], ['USER_FIELD_NAME' => 'UF_ROLE']);
        while ($enum = $rs->Fetch()) {
            $roles[$enum['VALUE']] = $enum['VALUE'];
        }
        return $roles;
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
            'UF_ROLE' => $fields['UF_ROLE'],
            'UF_CAFEDRA_NAME' => $this->getIblockElementName($this->arParams['IBLOCK_CAFEDRA_ID'], $fields['UF_CAFEDRA']),
            'UF_GROUP_NAME' => $this->getIblockElementName($this->arParams['IBLOCK_GROUP_ID'], $fields['UF_GROUP']),
            'EMAIL_TO' => $adminEmail,
        ];

        \CEvent::Send('CUSTOM_NEW_USER_NEED_ACTIVATION', SITE_ID, $arEventFields);
    }

    /**
     * Вспомогательная функция для получения названия элемента
     */
    protected function getIblockElementName(int $iblockId, int $elementId): string
    {
        $el = CIBlockElement::GetByID($elementId)->Fetch();
        return $el['NAME'] ?? '';
    }

    // Реализация интерфейсов Errorable
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): ?Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function configureActions(): array
    {
        return [];
    }
}