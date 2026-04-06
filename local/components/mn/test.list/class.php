<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Iblock\Elements\ElementPsychoTestsTable;

class MnTestListComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_TESTS_ID'] = (int)($params['IBLOCK_TESTS_ID'] ?? 0);
        $params['CATEGORY_ID'] = (int)($params['CATEGORY_ID'] ?? 0);
        $params['PAGE_SIZE'] = (int)($params['PAGE_SIZE'] ?? 10);
        return $params;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль инфоблоков не установлен');
            return;
        }

        $this->arResult['ITEMS'] = $this->getTests();
        $this->arResult['NAV_STRING'] = $this->getNavString();

        $this->includeComponentTemplate();
    }

    protected function getTests()
    {
        $iblockId = $this->arParams['IBLOCK_TESTS_ID'];
        if (!$iblockId) return [];

        $nav = new \CIBlockResult();
        $nav->NavStart($this->arParams['PAGE_SIZE']);

        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
        if ($this->arParams['CATEGORY_ID'] > 0) {
            $filter['PROPERTY_CATEGORY'] = $this->arParams['CATEGORY_ID'];
        }


        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'NAME' => 'ASC'],
            $filter,
            false,
            $nav,
            ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'DETAIL_PAGE_URL', 'PROPERTY_CATEGORY']
        );

        $items = [];
        while ($item = $res->GetNext()) {
            $item['DETAIL_URL'] = str_replace('#ELEMENT_ID#', $item['ID'], $this->arParams['DETAIL_URL']);
            $items[] = $item;
        }

        $this->arResult['NAV_RESULT'] = $nav;
        return $items;
    }

    protected function getNavString()
    {
        if ($this->arResult['NAV_RESULT']) {
            return $this->arResult['NAV_RESULT']->GetPageNavString('Тесты:', 'modern');
        }
        return '';
    }
}