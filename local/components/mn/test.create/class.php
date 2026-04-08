<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

class MnTestCreateComponent extends CBitrixComponent
{
    protected $errors;
    protected $request;

    public function onIncludeComponentLang() {}

    public function onPrepareComponentParams($params)
    {
        $this->errors = new ErrorCollection();
        $params['IBLOCK_CATEGORIES_ID'] = (int)($params['IBLOCK_CATEGORIES_ID'] ?? $params['~IBLOCK_CATEGORIES_ID'] ?? 0);
        $params['IBLOCK_TESTS_ID'] = (int)($params['IBLOCK_TESTS_ID'] ?? $params['~IBLOCK_TESTS_ID'] ?? 0);
        $params['IBLOCK_QUESTIONS_ID'] = (int)($params['IBLOCK_QUESTIONS_ID'] ?? $params['~IBLOCK_QUESTIONS_ID'] ?? 0);
        $params['IBLOCK_OPTIONS_ID'] = (int)($params['IBLOCK_OPTIONS_ID'] ?? $params['~IBLOCK_OPTIONS_ID'] ?? 0);
        return $params;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль инфоблоков не установлен');
            return;
        }

        $this->request = Context::getCurrent()->getRequest();

        $this->arResult['IBLOCK_CATEGORIES_ID'] = $this->arParams['IBLOCK_CATEGORIES_ID'];
        $this->arResult['IBLOCK_TESTS_ID'] = $this->arParams['IBLOCK_TESTS_ID'];
        $this->arResult['IBLOCK_QUESTIONS_ID'] = $this->arParams['IBLOCK_QUESTIONS_ID'];
        $this->arResult['IBLOCK_OPTIONS_ID'] = $this->arParams['IBLOCK_OPTIONS_ID'];
        $this->arResult['CATEGORIES'] = $this->getCategories();

        if ($this->request->isAjaxRequest() && $this->request->getPost('action') === 'saveTest') {
            $this->handleSaveTest();
            return;
        }

        $this->includeComponentTemplate();
    }

    protected function getCategories()
    {
        $iblockId = $this->arResult['IBLOCK_CATEGORIES_ID'];
        if (!$iblockId) return [];
        $res = CIBlockElement::GetList(
            ['NAME' => 'ASC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME']
        );
        $categories = [];
        while ($item = $res->Fetch()) {
            $categories[] = $item;
        }
        return $categories;
    }

    protected function handleSaveTest()
    {
        $post = $this->request->getPostList()->toArray();

        $testName = trim($post['testName'] ?? '');
        if (empty($testName)) {
            $this->sendJsonError('Введите название теста');
        }

        $categoryId = (int)($post['categoryId'] ?? 0);
        if (!$categoryId) {
            $this->sendJsonError('Выберите категорию');
        }

        $description = trim($post['description'] ?? '');
        $instruction = trim($post['instruction'] ?? '');

        $questionsData = json_decode($post['questionsData'] ?? '', true);
        if (empty($questionsData)) {
            $this->sendJsonError('Нет вопросов');
        }

        $catIblockId = $this->arResult['IBLOCK_CATEGORIES_ID'];
        $testsIblockId = $this->arResult['IBLOCK_TESTS_ID'];
        $questionsIblockId = $this->arResult['IBLOCK_QUESTIONS_ID'];
        $optionsIblockId = $this->arResult['IBLOCK_OPTIONS_ID'];

        if (!$catIblockId) $this->sendJsonError('Не задан ID инфоблока категорий');
        if (!$testsIblockId) $this->sendJsonError('Не задан ID инфоблока тестов');
        if (!$questionsIblockId) $this->sendJsonError('Не задан ID инфоблока вопросов');
        if (!$optionsIblockId) $this->sendJsonError('Не задан ID инфоблока вариантов');

        if (!CIBlock::GetByID($catIblockId)->Fetch()) $this->sendJsonError("Инфоблок категорий с ID {$catIblockId} не существует");
        if (!CIBlock::GetByID($testsIblockId)->Fetch()) $this->sendJsonError("Инфоблок тестов с ID {$testsIblockId} не существует");
        if (!CIBlock::GetByID($questionsIblockId)->Fetch()) $this->sendJsonError("Инфоблок вопросов с ID {$questionsIblockId} не существует");
        if (!CIBlock::GetByID($optionsIblockId)->Fetch()) $this->sendJsonError("Инфоблок вариантов с ID {$optionsIblockId} не существует");

        $categoryExists = CIBlockElement::GetByID($categoryId)->Fetch();
        if (!$categoryExists) {
            $this->sendJsonError("Категория с ID {$categoryId} не найдена");
        }

        $rights = CIBlock::GetPermission($testsIblockId);
        if ($rights < 'W') {
            $this->sendJsonError("Недостаточно прав для добавления теста (требуется запись)");
        }

        $testId = $this->createTest($testName, $description, $instruction, $categoryId);
        if (!$testId) {
            $errorMsg = $this->errors->current() ? $this->errors->current()->getMessage() : 'Неизвестная ошибка';
            $this->sendJsonError('Ошибка создания теста: ' . $errorMsg);
        }

        $questionIds = [];
        foreach ($questionsData as $idx => $q) {
            $questionId = $this->createQuestion($testId, $q);
            if ($questionId) {
                $questionIds[] = $questionId;
                if (!empty($q['options']) && is_array($q['options'])) {
                    $sort = 10;
                    foreach ($q['options'] as $optIdx => $opt) {
                        $optionId = $this->createOption($questionId, $opt['text'], $opt['score'], $sort);
                        if (!$optionId) {
                            foreach ($questionIds as $qid) CIBlockElement::Delete($qid);
                            CIBlockElement::Delete($testId);
                            $errorMsg = $this->errors->current() ? $this->errors->current()->getMessage() : 'Ошибка создания варианта';
                            $this->sendJsonError("Ошибка создания варианта #{$optIdx} для вопроса #{$idx}: " . $errorMsg);
                        }
                        $sort += 10;
                    }
                }
            } else {
                foreach ($questionIds as $qid) CIBlockElement::Delete($qid);
                CIBlockElement::Delete($testId);
                $errorMsg = $this->errors->current() ? $this->errors->current()->getMessage() : 'Ошибка создания вопроса';
                $this->sendJsonError("Ошибка создания вопроса #{$idx}: " . $errorMsg);
            }
        }

        $this->sendJsonSuccess(['testId' => $testId, 'message' => 'Тест успешно создан!']);
    }

    protected function createTest($name, $description, $instruction, $categoryId)
    {
        $iblockId = $this->arResult['IBLOCK_TESTS_ID'];
        $el = new CIBlockElement();

        $code = \CUtil::translit($name, "ru", ["replace_space" => "-", "replace_other" => "-"]);
        $originalCode = $code;
        $i = 1;
        while (\CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch()) {
            $code = $originalCode . '-' . $i++;
        }

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'CODE' => $code,
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'CATEGORY'    => $categoryId,
                'DESCRIPTION' => $description,
                'INSTRUCTION' => $instruction,
            ],
        ];

        $testId = $el->Add($fields);
        if (!$testId) {
            $this->errors[] = new Error($el->LAST_ERROR);
            return false;
        }
        return $testId;
    }

    protected function createQuestion($testId, $questionData)
    {
        $iblockId = $this->arResult['IBLOCK_QUESTIONS_ID'];
        $el = new CIBlockElement();

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $questionData['text'],
            'ACTIVE' => 'Y',
        ];

        $questionId = $el->Add($fields);
        if (!$questionId) {
            $this->errors[] = new Error($el->LAST_ERROR);
            return false;
        }

        $propertyValues = ['TEST_ID' => $testId];

        $questionTypeValue = $questionData['type'];
        $propRes = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => 'QUESTION_TYPE']);
        if ($prop = $propRes->Fetch()) {
            if ($prop['PROPERTY_TYPE'] == 'L') {
                $enumRes = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $prop['ID'], 'XML_ID' => $questionTypeValue]);
                if ($enum = $enumRes->Fetch()) {
                    $propertyValues['QUESTION_TYPE'] = $enum['ID'];
                } else {
                    $this->errors[] = new Error("Не найдено значение списка для типа вопроса '{$questionTypeValue}'");
                    CIBlockElement::Delete($questionId);
                    return false;
                }
            } else {
                $propertyValues['QUESTION_TYPE'] = $questionTypeValue;
            }
        }

        CIBlockElement::SetPropertyValuesEx($questionId, $iblockId, $propertyValues);

        if (!empty($questionData['image']) && strpos($questionData['image'], 'data:image') === 0) {
            $imageFileId = $this->base64ToFile($questionData['image'], 'question_image');
            if ($imageFileId) {
                CIBlockElement::SetPropertyValueCode($questionId, 'IMAGE', $imageFileId);
            }
        }

        return $questionId;
    }

    protected function createOption($questionId, $text, $score, $sort = 10)
    {
        $iblockId = $this->arResult['IBLOCK_OPTIONS_ID'];
        $el = new CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $text,
            'ACTIVE' => 'Y',
            'SORT' => $sort,
            'PROPERTY_VALUES' => [
                'QUESTION_ID' => $questionId,
                'SCORE' => (float)$score,
            ],
        ];
        $optionId = $el->Add($fields);
        if (!$optionId) {
            $this->errors[] = new Error($el->LAST_ERROR);
            return false;
        }
        return $optionId;
    }

    protected function base64ToFile($base64String, $prefix = 'img')
    {
        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64String, $matches)) {
            return false;
        }
        $ext = $matches[1];
        $encoded = $matches[2];
        $decoded = base64_decode($encoded);
        if (!$decoded) {
            return false;
        }

        $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0777, true)) {
                return false;
            }
        }

        $tempPath = $tempDir . $prefix . '_' . md5(uniqid()) . '.' . $ext;
        if (file_put_contents($tempPath, $decoded) === false) {
            return false;
        }

        $arFile = CFile::MakeFileArray($tempPath);
        if (!$arFile) {
            @unlink($tempPath);
            return false;
        }

        $arFile['MODULE_ID'] = 'iblock';
        $fileId = CFile::SaveFile($arFile, 'iblock');
        @unlink($tempPath);

        return $fileId;
    }

    protected function sendJsonError($message)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');
        echo Json::encode(['success' => false, 'error' => $message]);
        die();
    }

    protected function sendJsonSuccess($data = [])
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');
        echo Json::encode(array_merge(['success' => true], $data));
        die();
    }
}