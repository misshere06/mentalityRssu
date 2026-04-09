<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\UserTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;

class UsersListComponent extends CBitrixComponent implements Controllerable
{
    // Конфигурация для AJAX-действий
    public function configureActions()
    {
        return [
            'getUserTests' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(['POST']),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        if (!is_array($arParams["GROUPS_IDS"])) {
            $arParams["GROUPS_IDS"] = [];
        }
        $arParams["USERS_PER_PAGE"] = (int)$arParams["USERS_PER_PAGE"] ?: 20;
        return $arParams;
    }

    public function executeComponent()
    {
        Loader::includeModule('highloadblock');
        Loader::includeModule('iblock');

        if (empty($this->arParams["GROUPS_IDS"])) {
            $this->arResult["USERS"] = [];
            $this->includeComponentTemplate();
            return;
        }

        $this->arResult["USERS"] = $this->getUsers();
        $this->includeComponentTemplate();
    }

    private function getUsers()
    {
        $users = [];
        $nav = new \Bitrix\Main\UI\PageNavigation("users_nav");
        $nav->allowAllRecords(false)
            ->setPageSize($this->arParams["USERS_PER_PAGE"])
            ->initFromUri();

        // Получаем ID пользователей из указанных групп
        $groupUsersQuery = UserGroupTable::query()
            ->setSelect(['USER_ID'])
            ->whereIn('GROUP_ID', $this->arParams["GROUPS_IDS"])
            ->setGroup(['USER_ID']);

        $userIds = array_column($groupUsersQuery->exec()->fetchAll(), 'USER_ID');
        if (empty($userIds)) {
            return [];
        }

        // Основной запрос с пагинацией
        $query = UserTable::query()
            ->setSelect([
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHOTO',
                'UF_ROLE', 'UF_SPECIALNOST', 'UF_GROUP'
            ])
            ->whereIn('ID', $userIds)
            ->setOffset($nav->getOffset())
            ->setLimit($nav->getLimit())
            ->setOrder(['LAST_NAME' => 'ASC']);

        $countQuery = clone $query;
        $totalCount = $countQuery->queryCountTotal();
        $nav->setRecordCount($totalCount);
        $this->arResult["NAV_OBJECT"] = $nav;

        $result = $query->exec();
        while ($user = $result->fetch()) {
            $photoPath = '';
            if ($user['PERSONAL_PHOTO']) {
                $photoPath = \CFile::GetPath($user['PERSONAL_PHOTO']);
            }

            $users[] = [
                'ID' => $user['ID'],
                'FULL_NAME' => trim("{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}"),
                'ROLE' => $user['UF_ROLE'] ?? '',
                'SPECIALTY' => $user['UF_SPECIALNOST'] ?? '',
                'GROUP' => $user['UF_GROUP'] ?? '',
                'PHOTO' => $photoPath ?: '/local/templates/.default/images/no_photo.png',
            ];
        }

        return $users;
    }

    /**
     * AJAX-действие: получение тестов пользователя
     */
    public function getUserTestsAction($userId)
    {
        Loader::includeModule('highloadblock');
        Loader::includeModule('iblock');

        $userId = (int)$userId;
        if ($userId <= 0) {
            $this->addError(new \Bitrix\Main\Error('Неверный ID пользователя'));
            return null;
        }

        try {
            // 1. Highload-блок
            $hlblock = HighloadBlockTable::getById(1)->fetch();
            if (!$hlblock) {
                $this->addError(new \Bitrix\Main\Error('Highload-блок с ID=1 не найден'));
                return null;
            }

            $entity = HighloadBlockTable::compileEntity($hlblock);
            $entityClass = $entity->getDataClass();

            $testResults = $entityClass::getList([
                'select' => ['UF_TEST_ID', 'UF_STATUS', 'UF_SCORE', 'UF_DATE_UPDATE'],
                'filter' => ['UF_USER_ID' => $userId],
                'order' => ['UF_DATE_UPDATE' => 'DESC']
            ])->fetchAll();

            $testIds = array_unique(array_column($testResults, 'UF_TEST_ID'));

            // 2. Информация о тестах из инфоблока
            $testsInfo = [];
            if (!empty($testIds)) {
                $elements = ElementTable::getList([
                    'select' => ['ID', 'NAME'],
                    'filter' => ['IBLOCK_ID' => 6, 'ID' => $testIds],
                ]);
                while ($elem = $elements->fetch()) {
                    $testsInfo[$elem['ID']] = $elem['NAME'];
                }
            }

            // 3. Формируем результат
            $tests = [];
            foreach ($testResults as $tr) {
                $testId = $tr['UF_TEST_ID'];
                $tests[] = [
                    'NAME' => $testsInfo[$testId] ?? "Тест #{$testId}",
                    'STATUS' => $tr['UF_STATUS'] ?? 'неизвестно',
                    'SCORE' => $tr['UF_SCORE'] !== null ? (float)$tr['UF_SCORE'] : '—',
                    'DATE' => $tr['UF_DATE_UPDATE'] ? $tr['UF_DATE_UPDATE']->format('d.m.Y H:i') : '',
                ];
            }

            // 4. Пользователь
            $user = UserTable::getById($userId)->fetch();
            if (!$user) {
                $this->addError(new \Bitrix\Main\Error('Пользователь не найден'));
                return null;
            }

            $photoPath = '';
            if (!empty($user['PERSONAL_PHOTO'])) {
                $photoPath = \CFile::GetPath($user['PERSONAL_PHOTO']);
            }
            $userName = trim("{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}");

            return [
                'tests' => $tests,
                'userPhoto' => $photoPath ?: '/local/templates/.default/images/no_photo.png',
                'userName' => $userName,
            ];

        } catch (\Exception $e) {
            // Логируем ошибку в файл (убедитесь, что папка /local/log/ существует и доступна для записи)
            $logDir = $_SERVER['DOCUMENT_ROOT'] . '/local/log/';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents(
                $logDir . 'users_list_errors.log',
                date('Y-m-d H:i:s') . " getUserTestsAction ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n",
                FILE_APPEND
            );

            $this->addError(new \Bitrix\Main\Error('Внутренняя ошибка сервера: ' . $e->getMessage()));
            return null;
        }
    }
}