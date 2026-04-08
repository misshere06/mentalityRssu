<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\UI\PageNavigation;

class MnTestRedactComponent extends CBitrixComponent
{
    const IBLOCK_TESTS = 6;
    const IBLOCK_QUESTIONS = 7;
    const IBLOCK_ANSWERS = 8;
    const IBLOCK_CATEGORIES = 5;

    private static $questionTypeEnumCache = [];

    public function executeComponent()
    {
        global $APPLICATION;
        Loader::includeModule('iblock');

        $request = Context::getCurrent()->getRequest();
        $action = $request->get('action') ?: 'list';
        $testId = (int)$request->get('test_id');

        // Обработка удаления теста
        if ($action === 'delete' && $testId > 0) {
            if (check_bitrix_sessid()) {
                $this->deleteAllQuestionsAndAnswers($testId);
                \CIBlockElement::Delete($testId);
            }
            LocalRedirect($APPLICATION->GetCurPageParam('', ['action', 'test_id']));
        }

        if ($action === 'edit' && $testId > 0) {
            $this->arResult['MODE'] = 'edit';
            $this->arResult['TEST_ID'] = $testId;
            $this->arResult['TEST'] = $this->getTestData($testId);
            $this->arResult['QUESTIONS'] = $this->getQuestionsWithAnswers($testId);
            $this->arResult['CATEGORIES'] = $this->getCategoryList();
            $this->arResult['STATUS_LIST'] = [
                'DRAFT' => 'Не опубликован',
                'PUBLISHED' => 'Опубликован',
            ];

            if ($request->isPost() && check_bitrix_sessid()) {
                $this->saveTest($testId, $request);
                LocalRedirect($APPLICATION->GetCurPageParam('action=list', ['action', 'test_id']));
            }

            $this->includeComponentTemplate('edit');
        } else {
            $this->arResult['MODE'] = 'list';
            $nav = new PageNavigation('nav-tests');
            $nav->allowAllRecords(true)
                ->setPageSize(10)
                ->initFromUri();

            $filter = $this->prepareFilter($request);
            $tests = $this->getTestList($filter, $nav);

            $this->arResult['TESTS'] = $tests;
            $this->arResult['NAV'] = $nav;
            $this->arResult['STATUS_LIST'] = ['DRAFT' => 'Не опубликован', 'PUBLISHED' => 'Опубликован'];
            $this->arResult['CATEGORIES'] = $this->getCategoryList();

            $this->includeComponentTemplate();
        }
    }

    private function getTestData($testId)
    {
        $res = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => self::IBLOCK_TESTS, 'ID' => $testId],
            false,
            false,
            ['ID', 'NAME', 'ACTIVE', 'CODE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'PROPERTY_CATEGORY', 'PROPERTY_DESCRIPTION', 'PROPERTY_INSTRUCTION']
        );
        if ($test = $res->GetNext()) {
            $test['CATEGORY_ID'] = $test['PROPERTY_CATEGORY_VALUE'];
            $test['DESCRIPTION'] = $test['PROPERTY_DESCRIPTION_VALUE'];
            $test['INSTRUCTION'] = $test['PROPERTY_INSTRUCTION_VALUE'];
            $test['STATUS'] = $test['ACTIVE'] == 'Y' ? 'PUBLISHED' : 'DRAFT';
            return $test;
        }
        return null;
    }

    private function getQuestionsWithAnswers($testId)
    {
        $questions = [];
        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => self::IBLOCK_QUESTIONS, 'PROPERTY_TEST_ID' => $testId],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_QUESTION_TYPE', 'PROPERTY_IMAGE']
        );
        while ($q = $res->GetNext()) {
            // Получаем XML_ID типа вопроса (значение списка)
            $typeXmlId = $this->getQuestionTypeXmlById($q['PROPERTY_QUESTION_TYPE_VALUE']);
            $q['TYPE'] = $typeXmlId ?: $q['PROPERTY_QUESTION_TYPE_VALUE']; // fallback
            $q['IMAGE'] = $q['PROPERTY_IMAGE_VALUE'];

            $answers = [];
            $ansRes = \CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => self::IBLOCK_ANSWERS, 'PROPERTY_QUESTION_ID' => $q['ID']],
                false,
                false,
                ['ID', 'NAME', 'PROPERTY_SCORE']
            );
            while ($a = $ansRes->GetNext()) {
                $answers[] = [
                    'ID' => $a['ID'],
                    'TEXT' => $a['NAME'],
                    'SCORE' => $a['PROPERTY_SCORE_VALUE'],
                ];
            }
            $q['ANSWERS'] = $answers;
            $questions[] = $q;
        }
        return $questions;
    }

    /**
     * Возвращает XML_ID значения списка по ID
     */
    private function getQuestionTypeXmlById($enumId)
    {
        if (!$enumId) return null;
        static $cache = [];
        if (isset($cache[$enumId])) return $cache[$enumId];

        $enum = \CIBlockPropertyEnum::GetByID($enumId);
        if ($enum && $enum['XML_ID']) {
            $cache[$enumId] = $enum['XML_ID'];
            return $enum['XML_ID'];
        }
        return null;
    }

    /**
     * Возвращает ID значения списка по XML_ID
     */
    private function getQuestionTypeEnumId($xmlId)
    {
        if (isset(self::$questionTypeEnumCache[$xmlId])) {
            return self::$questionTypeEnumCache[$xmlId];
        }

        $property = \CIBlockProperty::GetList(
            [],
            ['IBLOCK_ID' => self::IBLOCK_QUESTIONS, 'CODE' => 'QUESTION_TYPE']
        )->Fetch();
        if (!$property) {
            return null;
        }

        $enum = \CIBlockPropertyEnum::GetList(
            [],
            ['PROPERTY_ID' => $property['ID'], 'XML_ID' => $xmlId]
        )->Fetch();
        if ($enum) {
            self::$questionTypeEnumCache[$xmlId] = $enum['ID'];
            return $enum['ID'];
        }
        return null;
    }

    private function getCategoryList()
    {
        $list = [];
        $res = \CIBlockElement::GetList(['NAME' => 'ASC'], ['IBLOCK_ID' => self::IBLOCK_CATEGORIES], false, false, ['ID', 'NAME']);
        while ($item = $res->GetNext()) {
            $list[$item['ID']] = $item['NAME'];
        }
        return $list;
    }

    private function prepareFilter($request)
    {
        $filter = ['IBLOCK_ID' => self::IBLOCK_TESTS];
        if ($search = trim($request->get('search'))) {
            $filter['%NAME'] = $search;
        }
        if ($status = $request->get('status')) {
            $filter['ACTIVE'] = ($status === 'PUBLISHED') ? 'Y' : 'N';
        }
        if ($category = (int)$request->get('category')) {
            $filter['PROPERTY_CATEGORY'] = $category;
        }
        if ($dateFrom = $request->get('date_from')) {
            $filter['>=DATE_ACTIVE_FROM'] = $dateFrom;
        }
        if ($dateTo = $request->get('date_to')) {
            $filter['<=DATE_ACTIVE_TO'] = $dateTo;
        }
        return $filter;
    }

    private function getTestList($filter, $nav)
    {
        $tests = [];

        $countRes = \CIBlockElement::GetList([], $filter, ['ID']);
        $totalCount = $countRes;
        $nav->setRecordCount($totalCount);

        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            $filter,
            false,
            ['nPageSize' => $nav->getLimit(), 'iNumPage' => $nav->getCurrentPage()],
            ['ID', 'NAME', 'ACTIVE', 'DATE_ACTIVE_FROM', 'PROPERTY_CATEGORY', 'PROPERTY_DESCRIPTION']
        );

        while ($row = $res->GetNext()) {
            $tests[] = [
                'ID' => $row['ID'],
                'NAME' => $row['NAME'],
                'ACTIVE' => $row['ACTIVE'],
                'DATE_ACTIVE_FROM' => $row['DATE_ACTIVE_FROM'],
                'CATEGORY' => $this->getCategoryName($row['PROPERTY_CATEGORY_VALUE']),
                'DESCRIPTION' => $row['PROPERTY_DESCRIPTION_VALUE'],
                'STATUS' => $row['ACTIVE'] == 'Y' ? 'PUBLISHED' : 'DRAFT',
            ];
        }
        return $tests;
    }

    private function getCategoryName($catId)
    {
        static $cache = [];
        if (!$catId) return '';
        if (!isset($cache[$catId])) {
            $res = \CIBlockElement::GetByID($catId);
            if ($item = $res->GetNext()) $cache[$catId] = $item['NAME'];
            else $cache[$catId] = '';
        }
        return $cache[$catId];
    }

    /**
     * Сохраняет тест: обновляет поля самого теста, удаляет все старые вопросы и ответы,
     * затем создаёт новые вопросы и ответы на основе переданных данных.
     */
    private function saveTest($testId, $request)
    {
        // 1. Обновляем поля теста
        $testFields = [
            'NAME' => trim($request->getPost('test_name')),
            'ACTIVE' => $request->getPost('status') === 'PUBLISHED' ? 'Y' : 'N',
            'DATE_ACTIVE_FROM' => $request->getPost('date_from') ?: false,
            'DATE_ACTIVE_TO' => $request->getPost('date_to') ?: false,
            'PROPERTY_VALUES' => [
                'CATEGORY' => (int)$request->getPost('category'),
                'DESCRIPTION' => $request->getPost('description'),
                'INSTRUCTION' => $request->getPost('instruction'),
            ]
        ];
        $el = new \CIBlockElement;
        if (!$el->Update($testId, $testFields)) {
            $this->arResult['ERROR'] = $el->LAST_ERROR;
            return;
        }

        // 2. Полностью удаляем все старые вопросы и ответы, связанные с этим тестом
        $this->deleteAllQuestionsAndAnswers($testId);

        // 3. Создаём новые вопросы и ответы из переданных данных
        $questionsData = json_decode($request->getPost('questions'), true);
        if (!is_array($questionsData)) {
            return;
        }

        $questionSort = 10;
        foreach ($questionsData as $qData) {
            $typeXmlId = $qData['type']; // 'radio', 'checkbox', 'select', 'text', 'textarea'
            $typeEnumId = $this->getQuestionTypeEnumId($typeXmlId);
            if (!$typeEnumId) {
                // Если тип вопроса не найден в справочнике, пропускаем вопрос
                continue;
            }

            $qFields = [
                'NAME' => $qData['text'],
                'IBLOCK_ID' => self::IBLOCK_QUESTIONS,
                'SORT' => $questionSort,
                'PROPERTY_VALUES' => [
                    'TEST_ID' => $testId,
                    'QUESTION_TYPE' => $typeEnumId,
                ]
            ];

            // Обработка изображения (base64)
            if (!empty($qData['image']) && strpos($qData['image'], 'data:') === 0) {
                $imageId = $this->saveBase64Image($qData['image']);
                if ($imageId) {
                    $qFields['PROPERTY_VALUES']['IMAGE'] = $imageId;
                }
            }

            $newQuestionId = $el->Add($qFields);
            if (!$newQuestionId) {
                // Логируем ошибку, но продолжаем создавать остальные вопросы
                AddMessage2Log("Ошибка создания вопроса: " . $el->LAST_ERROR, "test_redact");
                $questionSort += 10;
                continue;
            }

            // Создаём ответы (варианты) только для типов, у которых они есть
            if (in_array($typeXmlId, ['radio', 'checkbox', 'select']) && is_array($qData['answers'])) {
                $answerSort = 10;
                foreach ($qData['answers'] as $aData) {
                    $aFields = [
                        'NAME' => $aData['text'],
                        'IBLOCK_ID' => self::IBLOCK_ANSWERS,
                        'SORT' => $answerSort,
                        'PROPERTY_VALUES' => [
                            'QUESTION_ID' => $newQuestionId,
                            'SCORE' => (int)$aData['score'],
                        ]
                    ];
                    $el->Add($aFields);
                    $answerSort += 10;
                }
            }

            $questionSort += 10;
        }
    }

    /**
     * Удаляет все вопросы и связанные с ними ответы для указанного теста
     * @param int $testId ID теста
     */
    private function deleteAllQuestionsAndAnswers($testId)
    {
        // Получаем все вопросы теста
        $qRes = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => self::IBLOCK_QUESTIONS, 'PROPERTY_TEST_ID' => $testId],
            false,
            false,
            ['ID']
        );
        while ($q = $qRes->Fetch()) {
            // Удаляем ответы этого вопроса
            $ansRes = \CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => self::IBLOCK_ANSWERS, 'PROPERTY_QUESTION_ID' => $q['ID']],
                false,
                false,
                ['ID']
            );
            while ($ans = $ansRes->Fetch()) {
                \CIBlockElement::Delete($ans['ID']);
            }
            // Удаляем сам вопрос
            \CIBlockElement::Delete($q['ID']);
        }
    }

    private function saveBase64Image($base64)
    {
        // Разбираем data:image/png;base64,...
        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64, $matches)) {
            return null;
        }
        $ext = $matches[1];
        $encoded = $matches[2];
        $decoded = base64_decode($encoded);
        if (!$decoded) return null;

        $tempPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/img_' . md5(uniqid()) . '.' . $ext;
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        file_put_contents($tempPath, $decoded);

        $fileArray = \CFile::MakeFileArray($tempPath);
        $fileArray['MODULE_ID'] = 'iblock';
        $fileId = \CFile::SaveFile($fileArray, 'test_images');
        @unlink($tempPath);
        return $fileId ?: null;
    }
}