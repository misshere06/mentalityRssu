<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Engine\CurrentUser;

class MnTestListComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_TESTS_ID'] = (int)($params['IBLOCK_TESTS_ID'] ?? 0);
        $params['CATEGORY_ID'] = (int)($params['CATEGORY_ID'] ?? 0);
        $params['PAGE_SIZE'] = (int)($params['PAGE_SIZE'] ?? 10);
        // Фильтр по прохождению: all / actual / completed
        $params['FILTER'] = $_GET['filter'] ?? $params['FILTER'] ?? 'all';
        return $params;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('highloadblock')) {
            ShowError('Необходимые модули не установлены');
            return;
        }

        $this->arResult['ITEMS'] = $this->getTests();
        $this->arResult['NAV_STRING'] = $this->getNavString();
        $this->arResult['FILTER'] = $this->arParams['FILTER'];

        $this->includeComponentTemplate();
    }

    protected function getTests()
    {
        $iblockId = $this->arParams['IBLOCK_TESTS_ID'];
        if (!$iblockId) return [];

        // 1. Получаем все ID тестов (без учёта пагинации для фильтра)
        $allIds = $this->getAllTestIds($iblockId);

        // 2. Загружаем статусы прохождения текущего пользователя
        $userId = CurrentUser::get()->getId();
        $completedTestIds = [];
        $testStatusData = []; // testId => ['STATUS' => '', 'DATE' => '']
        if ($userId > 0 && !empty($allIds)) {
            $hlData = $this->getUserTestResults($userId, $allIds);
            $completedTestIds = $hlData['COMPLETED_IDS'];
            $testStatusData = $hlData['STATUS_DATA'];
        }

        // 3. Применяем фильтр
        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
        if ($this->arParams['CATEGORY_ID'] > 0) {
            $filter['PROPERTY_CATEGORY'] = $this->arParams['CATEGORY_ID'];
        }
        switch ($this->arParams['FILTER']) {
            case 'completed':
                if (empty($completedTestIds)) {
                    return []; // Нет пройденных — пустой результат
                }
                $filter['ID'] = $completedTestIds;
                break;
            case 'actual':
                if (!empty($completedTestIds)) {
                    $filter['!ID'] = $completedTestIds;
                }
                break;
            // 'all' — без изменений
        }

        // 4. Навигация и выборка
        $nav = new \CIBlockResult();
        $nav->NavStart($this->arParams['PAGE_SIZE']);

        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'NAME' => 'ASC'],
            $filter,
            false,
            $nav,
            ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PAGE_URL',
                'PROPERTY_CATEGORY', 'PROPERTY_DESCRIPTION', 'PROPERTY_INSTRUCTION']
        );

        $items = [];
        while ($item = $res->GetNext()) {
            // Универсальная замена макросов в URL
            $item['DETAIL_URL'] = str_replace(
                ['#ELEMENT_ID#', '#ELEMENT_CODE#'],
                [$item['ID'], $item['CODE']],
                $this->arParams['DETAIL_URL']
            );

            $item['PREVIEW_PICTURE_SRC'] = $item['PREVIEW_PICTURE']
                ? CFile::GetPath($item['PREVIEW_PICTURE']) : '';

            $item['DESCRIPTION'] = $item['PROPERTY_DESCRIPTION_VALUE'] ?? '';
            $item['INSTRUCTION'] = $item['PROPERTY_INSTRUCTION_VALUE'] ?? '';

            // Категория
            if (!empty($item['PROPERTY_CATEGORY_VALUE'])) {
                $cat = CIBlockElement::GetByID($item['PROPERTY_CATEGORY_VALUE']);
                if ($catElem = $cat->GetNext()) {
                    $item['CATEGORY_NAME'] = $catElem['NAME'];
                }
            }

            // Статус прохождения
            $testId = $item['ID'];
            $item['IS_COMPLETED'] = in_array($testId, $completedTestIds);
            $item['COMPLETED_DATE'] = $testStatusData[$testId]['DATE'] ?? '';

            $items[] = $item;
        }

        $this->arResult['NAV_RESULT'] = $nav;
        return $items;
    }

    protected function getAllTestIds($iblockId)
    {
        $ids = [];
        $res = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID']
        );
        while ($el = $res->Fetch()) {
            $ids[] = $el['ID'];
        }
        return $ids;
    }

    protected function getUserTestResults($userId, array $testIds)
    {
        $completedIds = [];
        $statusData = [];

        if (empty($testIds)) return ['COMPLETED_IDS' => [], 'STATUS_DATA' => []];

        $hlblock = HighloadBlockTable::getById(1)->fetch();
        if (!$hlblock) return ['COMPLETED_IDS' => [], 'STATUS_DATA' => []];

        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        $rs = $entityDataClass::getList([
            'filter' => [
                'UF_USER_ID' => $userId,
                'UF_TEST_ID' => $testIds,
            ],
            'select' => ['UF_TEST_ID', 'UF_STATUS', 'UF_DATE_UPDATE'],
        ]);

        while ($row = $rs->fetch()) {
            $tid = $row['UF_TEST_ID'];
            if ($row['UF_STATUS'] === 'completed') {
                $completedIds[] = $tid;
            }
            // Преобразуем дату в ISO-формат для JS
            $statusData[$tid] = [
                'STATUS' => $row['UF_STATUS'],
                'DATE' => $row['UF_DATE_UPDATE']
                    ? $row['UF_DATE_UPDATE']->format('Y-m-d\TH:i:s')
                    : '',
            ];
        }

        return [
            'COMPLETED_IDS' => $completedIds,
            'STATUS_DATA' => $statusData,
        ];
    }

    protected function getNavString()
    {
        if ($this->arResult['NAV_RESULT']) {
            return $this->arResult['NAV_RESULT']->GetPageNavString('Тесты:', 'modern');
        }
        return '';
    }
}