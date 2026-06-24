<?php
namespace Custom\Profile;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CUser;
use CFile;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class ProfileComponent extends \CBitrixComponent implements Controllerable, Errorable
{
    protected ErrorCollection $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();
        $arParams['USER_ID'] = (int)$arParams['USER_ID'] ?: $GLOBALS['USER']->GetID();
        return $arParams;
    }

    public function executeComponent()
    {
        global $USER, $APPLICATION;
        Loc::loadMessages(__FILE__);

        if (!$USER->IsAuthorized()) {
            LocalRedirect('/');
            return;
        }

        // Загружаем данные текущего пользователя (или указанного)
        $userId = $this->arParams['USER_ID'];
        $rsUser = CUser::GetByID($userId);
        if (!($arUser = $rsUser->Fetch())) {
            $this->arResult['ERRORS'][] = 'Пользователь не найден.';
            $this->includeComponentTemplate();
            return;
        }

        $this->arResult['USER'] = $arUser;
        $this->arResult['SUCCESS'] = false;
        $this->arResult['ERRORS'] = [];

        // Обработка формы
        if ($this->request->isPost() && check_bitrix_sessid()) {
            $this->processUpdate();
        }

        $this->includeComponentTemplate();
    }

    protected function processUpdate(): void
    {
        $userId = $this->arParams['USER_ID'];
        $fields = [
            'NAME' => trim($this->request->getPost('NAME')),
            'LAST_NAME' => trim($this->request->getPost('LAST_NAME')),
            'SECOND_NAME' => trim($this->request->getPost('SECOND_NAME')),
            'EMAIL' => trim($this->request->getPost('EMAIL')),
        ];

        // Пароль (опционально)
        $newPassword = $this->request->getPost('NEW_PASSWORD');
        $confirmPassword = $this->request->getPost('NEW_PASSWORD_CONFIRM');
        if (!empty($newPassword) || !empty($confirmPassword)) {
            if ($newPassword !== $confirmPassword) {
                $this->arResult['ERRORS'][] = 'Пароли не совпадают.';
                return;
            }
            if (strlen($newPassword) < 6) {
                $this->arResult['ERRORS'][] = 'Пароль должен быть не менее 6 символов.';
                return;
            }
            $fields['PASSWORD'] = $newPassword;
            $fields['CONFIRM_PASSWORD'] = $confirmPassword;
        }

        // Валидация основных полей
        if (empty($fields['NAME']) || empty($fields['LAST_NAME'])) {
            $this->arResult['ERRORS'][] = 'Имя и фамилия обязательны.';
        }
        if (!empty($fields['EMAIL']) && !check_email($fields['EMAIL'])) {
            $this->arResult['ERRORS'][] = 'Некорректный email.';
        }

        // Фото
        $photo = $this->preparePhoto();
        if ($photo === false) {
            $this->arResult['ERRORS'][] = 'Ошибка загрузки фото. Допустимы JPG, PNG до 5 МБ.';
            return;
        }
        if ($photo !== null) {
            $fields['PERSONAL_PHOTO'] = $photo; // массив, ядро само сохранит
        }

        if (!empty($this->arResult['ERRORS'])) {
            return;
        }

        $user = new CUser;
        if (!$user->Update($userId, $fields)) {
            $this->arResult['ERRORS'][] = $user->LAST_ERROR;
        } else {
            $this->arResult['SUCCESS'] = true;
            // Обновляем данные в arResult, чтобы форма показала актуальное
            $this->arResult['USER'] = array_merge($this->arResult['USER'], $fields);
            // Если фото обновилось, нужно подгрузить его актуальные данные
            if ($photo !== null) {
                // Получим ID из сохранённого пользователя
                $updatedUser = CUser::GetByID($userId)->Fetch();
                $this->arResult['USER']['PERSONAL_PHOTO'] = $updatedUser['PERSONAL_PHOTO'];
            }
        }
    }

    /**
     * @return array|null|false  массив для CUser::Update, null если файл не выбран, false при ошибке
     */
    protected function preparePhoto()
    {
        if (empty($_FILES['PROFILE_PHOTO']) || $_FILES['PROFILE_PHOTO']['error'] !== UPLOAD_ERR_OK) {
            return null; // файл не загружали
        }

        $file = $_FILES['PROFILE_PHOTO'];
        $maxSize = 5 * 1024 * 1024; // 5 МБ
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt) || $file['size'] > $maxSize) {
            return false;
        }

        $photo = CFile::MakeFileArray($file['tmp_name']);
        if (!is_array($photo)) {
            return false;
        }
        $photo['name'] = $file['name']; // оригинальное имя
        return $photo;
    }

    // Errorable
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