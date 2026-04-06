<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;

class MnTestDetailComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_TESTS_ID'] = (int)($params['IBLOCK_TESTS_ID'] ?? 0);
        $params['IBLOCK_QUESTIONS_ID'] = (int)($params['IBLOCK_QUESTIONS_ID'] ?? 0);
        $params['IBLOCK_OPTIONS_ID'] = (int)($params['IBLOCK_OPTIONS_ID'] ?? 0);
        $params['ELEMENT_ID'] = (int)($params['ELEMENT_ID'] ?? 0);
        $params['ELEMENT_CODE'] = trim($params['ELEMENT_CODE'] ?? '');

        // Если передан код, а ID нет – найдём ID по коду
        if ($params['ELEMENT_CODE'] && !$params['ELEMENT_ID']) {
            $res = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $params['IBLOCK_TESTS_ID'], 'CODE' => $params['ELEMENT_CODE']],
                false,
                false,
                ['ID']
            );
            if ($el = $res->Fetch()) {
                $params['ELEMENT_ID'] = $el['ID'];
            }
        }
        return $params;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль инфоблоков не установлен');
            return;
        }

        if ($this->arParams['ELEMENT_ID'] <= 0) {
            ShowError('Не указан ID теста');
            return;
        }

        $this->arResult['TEST'] = $this->getTest();
        if (!$this->arResult['TEST']) {
            ShowError('Тест не найден');
            return;
        }

        $this->arResult['QUESTIONS'] = $this->getQuestionsWithOptions();


        $this->includeComponentTemplate();
    }

    protected function getTest()
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->arParams['IBLOCK_TESTS_ID'],
                'ID' => $this->arParams['ELEMENT_ID'],
                'ACTIVE' => 'Y',
            ],
            false,
            false,
            ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );
        return $res->GetNext();
    }

    protected function getQuestionsWithOptions()
    {
        $questions = [];
        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->arParams['IBLOCK_QUESTIONS_ID'],
                'ACTIVE' => 'Y',
                'PROPERTY_TEST_ID' => $this->arParams['ELEMENT_ID'],
            ],
            false,
            false,
            ['ID', 'NAME', 'PREVIEW_PICTURE', 'PROPERTY_QUESTION_TYPE']
        );

        while ($q = $res->GetNext()) {
            $q['OPTIONS'] = $this->getOptions($q['ID']);
            $questions[] = $q;
        }
        return $questions;
    }

    protected function getOptions($questionId)
    {
        $options = [];
        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->arParams['IBLOCK_OPTIONS_ID'],
                'ACTIVE' => 'Y',
                'PROPERTY_QUESTION_ID' => $questionId,
            ],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_SCORE']
        );
        while ($opt = $res->GetNext()) {
            $options[] = $opt;
        }
        return $options;
    }
}