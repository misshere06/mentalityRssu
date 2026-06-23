<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

class UsersListComponent extends CBitrixComponent implements Controllerable
{
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
        // ID инфоблоков для кафедры, специальности, учебной группы
        $arParams["CAFEDRA_IBLOCK_ID"] = (int)($arParams["CAFEDRA_IBLOCK_ID"] ?? 0);
        $arParams["SPECIALTY_IBLOCK_ID"] = (int)($arParams["SPECIALTY_IBLOCK_ID"] ?? 0);
        $arParams["GROUP_IBLOCK_ID"] = (int)($arParams["GROUP_IBLOCK_ID"] ?? 0);
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
                'UF_ROLE', 'UF_CAFEDRA', 'UF_SPECIALNOST', 'UF_GROUP'
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
        $rawUsers = [];
        while ($user = $result->fetch()) {
            $rawUsers[] = $user;
        }

        // Получаем текстовые значения для UF_ROLE (пользовательское поле типа "список")
        $roles = $this->getEnumValues('UF_ROLE', $rawUsers);

        // Получаем названия элементов инфоблоков для кафедры, специальности, группы
        $refs = $this->resolveReferences($rawUsers);

        // Формируем итоговый массив
        foreach ($rawUsers as $user) {
            $photoPath = '';
            if ($user['PERSONAL_PHOTO']) {
                $photoPath = \CFile::GetPath($user['PERSONAL_PHOTO']);
            }

            $users[] = [
                'ID' => $user['ID'],
                'FULL_NAME' => trim("{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}"),
                'ROLE' => $roles[$user['UF_ROLE']] ?? $user['UF_ROLE'] ?? '',
                'CAFEDRA' => $refs['cafedras'][$user['UF_CAFEDRA']] ?? $user['UF_CAFEDRA'] ?? '',
                'SPECIALTY' => $refs['specialties'][$user['UF_SPECIALNOST']] ?? $user['UF_SPECIALNOST'] ?? '',
                'GROUP' => $refs['groups'][$user['UF_GROUP']] ?? $user['UF_GROUP'] ?? '',
                'PHOTO' => $photoPath ?: '/local/templates/.default/images/no_photo.png',
            ];
        }

        return $users;
    }

    /**
     * Разрешает ссылки на элементы инфоблоков для кафедр, специальностей и учебных групп
     * @param array $users
     * @return array ['cafedras' => [ID => NAME], 'specialties' => [ID => NAME], 'groups' => [ID => NAME]]
     */
    private function resolveReferences(array $users)
    {
        $cafedraIds = [];
        $specIds = [];
        $groupIds = [];

        foreach ($users as $user) {
            if (!empty($user['UF_CAFEDRA'])) {
                $cafedraIds[] = $user['UF_CAFEDRA'];
            }
            if (!empty($user['UF_SPECIALNOST'])) {
                $specIds[] = $user['UF_SPECIALNOST'];
            }
            if (!empty($user['UF_GROUP'])) {
                $groupIds[] = $user['UF_GROUP'];
            }
        }

        $result = [
            'cafedras' => [],
            'specialties' => [],
            'groups' => []
        ];

        if (!empty($cafedraIds) && $this->arParams['CAFEDRA_IBLOCK_ID'] > 0) {
            $result['cafedras'] = $this->getElementNames($this->arParams['CAFEDRA_IBLOCK_ID'], array_unique($cafedraIds));
        }
        if (!empty($specIds) && $this->arParams['SPECIALTY_IBLOCK_ID'] > 0) {
            $result['specialties'] = $this->getElementNames($this->arParams['SPECIALTY_IBLOCK_ID'], array_unique($specIds));
        }
        if (!empty($groupIds) && $this->arParams['GROUP_IBLOCK_ID'] > 0) {
            $result['groups'] = $this->getElementNames($this->arParams['GROUP_IBLOCK_ID'], array_unique($groupIds));
        }

        return $result;
    }

    /**
     * Получает массив [ID => NAME] для указанных ID элементов инфоблока
     */
    private function getElementNames(int $iblockId, array $elementIds): array
    {
        if (empty($elementIds)) {
            return [];
        }

        $names = [];
        $elements = ElementTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => ['IBLOCK_ID' => $iblockId, 'ID' => $elementIds],
        ]);
        while ($el = $elements->fetch()) {
            $names[$el['ID']] = $el['NAME'];
        }
        return $names;
    }

    /**
     * Получает текстовые значения для пользовательского поля типа "список"
     * @param string $fieldName
     * @param array $users
     * @return array [ID => VALUE]
     */
    private function getEnumValues(string $fieldName, array $users): array
    {
        $ids = [];
        foreach ($users as $user) {
            if (!empty($user[$fieldName])) {
                $ids[] = $user[$fieldName];
            }
        }
        if (empty($ids)) {
            return [];
        }

        $values = [];
        $enum = \CUserFieldEnum::GetList([], ['USER_FIELD_NAME' => $fieldName, 'ID' => array_unique($ids)]);
        while ($item = $enum->Fetch()) {
            $values[$item['ID']] = $item['VALUE'];
        }
        return $values;
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
            $hlblock = HighloadBlockTable::getById(1)->fetch();
            if (!$hlblock) {
                $this->addError(new \Bitrix\Main\Error('Highload-блок с ID=1 не найден'));
                return null;
            }

            $entity = HighloadBlockTable::compileEntity($hlblock);
            $entityClass = $entity->getDataClass();

            $testResults = $entityClass::getList([
                'select' => ['UF_TEST_ID', 'UF_STATUS', 'UF_SCORE', 'UF_DATE_UPDATE'],
                'filter' => [
                    'UF_USER_ID' => $userId,
                    'UF_STATUS' => 'completed',
                ],
                'order' => ['UF_DATE_UPDATE' => 'DESC'],
            ])->fetchAll();

            if (empty($testResults)) {
                return [
                    'tests' => [],
                    'userPhoto' => $this->getUserPhoto($userId),
                    'userName' => $this->getUserName($userId),
                ];
            }

            $testIds = array_unique(array_column($testResults, 'UF_TEST_ID'));
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

            $tests = [];
            foreach ($testResults as $tr) {
                $testId = $tr['UF_TEST_ID'];
                $tests[] = [
                    'NAME' => $testsInfo[$testId] ?? "Тест #{$testId}",
                    'STATUS' => $tr['UF_STATUS'],
                    'SCORE' => $tr['UF_SCORE'] !== null ? (float)$tr['UF_SCORE'] : '—',
                    'DATE' => $tr['UF_DATE_UPDATE'] ? $tr['UF_DATE_UPDATE']->format('d.m.Y H:i') : '',
                ];
            }

            return [
                'tests' => $tests,
                'userPhoto' => $this->getUserPhoto($userId),
                'userName' => $this->getUserName($userId),
            ];

        } catch (\Exception $e) {
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

    private function getUserPhoto($userId)
    {
        $user = UserTable::getById($userId)->fetch();
        if ($user && !empty($user['PERSONAL_PHOTO'])) {
            return \CFile::GetPath($user['PERSONAL_PHOTO']);
        }
        return '/local/templates/.default/images/no_photo.png';
    }

    private function getUserName($userId)
    {
        $user = UserTable::getById($userId)->fetch();
        if ($user) {
            return trim("{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}");
        }
        return 'Пользователь';
    }
}