<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity;

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
        $this->request = Context::getCurrent()->getRequest();

        // Более надёжная проверка AJAX: если есть POST-параметр action
        $isAjax = $this->request->isAjaxRequest() || $this->request->getPost('action');

        if ($isAjax && $this->request->getPost('action')) {
            // Обязательно подключаем highloadblock для AJAX
            if (!Loader::includeModule('highloadblock')) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Модуль highloadblock не установлен']);
                return;
            }
            $this->handleAjax();
            return;
        }



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

        $this->arResult['USER_PROGRESS'] = $this->getUserProgress();

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
    protected function getUserProgress()
    {
        if (!$this->getUserId()) return null;

        $hlblock = $this->getHighloadBlock();
        if (!$hlblock) return null;

        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $result = $entityClass::getList([
            'filter' => [
                'UF_USER_ID' => $this->getUserId(),
                'UF_TEST_ID' => $this->arParams['ELEMENT_ID'],
            ],
            'limit' => 1,
        ]);
        if ($row = $result->fetch()) {
            return [
                'currentQuestion' => (int)$row['UF_CURRENT_QUESTION'],
                'answers' => json_decode($row['UF_ANSWERS'], true) ?: [],
                'status' => $row['UF_STATUS'],
                'score' => (int)$row['UF_SCORE'],
            ];
        }
        return null;
    }

    protected function saveUserProgress($currentIndex, $answers, $status = 'in_progress', $score = 0)
    {
        $userId = $this->getUserId();
        if (!$userId) return false;

        $hlblock = $this->getHighloadBlock();
        if (!$hlblock) return false;

        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();


        $existing = $entityClass::getList([
            'filter' => [
                'UF_USER_ID' => $userId,
                'UF_TEST_ID' => $this->arParams['ELEMENT_ID'],
            ],
            'limit' => 1,
        ])->fetch();

        $data = [
            'UF_USER_ID' => $userId,
            'UF_TEST_ID' => $this->arParams['ELEMENT_ID'],
            'UF_CURRENT_QUESTION' => $currentIndex,
            'UF_ANSWERS' => json_encode($answers, JSON_UNESCAPED_UNICODE),
            'UF_STATUS' => $status,
            'UF_SCORE' => $score,
            'UF_DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
        ];

        if ($existing) {
            $result = $entityClass::update($existing['ID'], $data);
        } else {
            $result = $entityClass::add($data);
        }
        return $result->isSuccess();
    }

    protected function getUserId()
    {
        global $USER;
        return $USER->IsAuthorized() ? $USER->GetID() : 0;
    }

    protected function getHighloadBlock()
    {
        if (!Loader::includeModule('highloadblock')) return null;
        $hlblock = HighloadBlockTable::getList(['filter' => ['NAME' => 'UserTestResults']])->fetch();
        return $hlblock ?: null;
    }

    protected function handleAjax()
    {
        error_reporting(0);

        $action = $this->request->getPost('action');


        switch ($action) {
            case 'saveAnswer':
                $this->saveAnswerAjax();
                break;
            case 'getProgress':
                $this->getProgressAjax();
                break;
            case 'completeTest':
                $this->completeTestAjax();
                break;
        }
    }

    protected function saveAnswerAjax()
    {
        $questionId = (int)$this->request->getPost('questionId');
        $answerValue = $this->request->getPost('answerValue');
        $currentIndex = (int)$this->request->getPost('currentIndex');
        $answersJson = $this->request->getPost('answers');
        $answers = $answersJson ? json_decode($answersJson, true) : [];

        $answers[$questionId] = $answerValue;
        $success = $this->saveUserProgress($currentIndex, $answers);
        $this->sendJsonResponse(['success' => $success]);
    }

    protected function getProgressAjax()
    {
        $progress = $this->getUserProgress();
        $this->sendJsonResponse(['success' => true, 'data' => $progress]);
    }

    protected function completeTestAjax()
    {
        $totalScore = (int)$this->request->getPost('totalScore');
        $answersJson = $this->request->getPost('answers');
        $answers = $answersJson ? json_decode($answersJson, true) : [];
        $totalQuestions = (int)$this->request->getPost('totalQuestions'); // добавить
        $lastIndex = $totalQuestions - 1;
        $this->saveUserProgress($lastIndex, $answers, 'completed', $totalScore);
        $this->sendJsonResponse(['success' => true]);
    }

    protected function sendJsonResponse($data)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');
        echo \Bitrix\Main\Web\Json::encode($data);
        die();
    }

}