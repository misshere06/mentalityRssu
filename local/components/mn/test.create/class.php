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
        // Приводим параметры к int (с учетом ключей с тильдой)
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

        // ВСЕГДА заполняем arResult ДО обработки AJAX
        $this->arResult['IBLOCK_CATEGORIES_ID'] = $this->arParams['IBLOCK_CATEGORIES_ID'];
        $this->arResult['IBLOCK_TESTS_ID'] = $this->arParams['IBLOCK_TESTS_ID'];
        $this->arResult['IBLOCK_QUESTIONS_ID'] = $this->arParams['IBLOCK_QUESTIONS_ID'];
        $this->arResult['IBLOCK_OPTIONS_ID'] = $this->arParams['IBLOCK_OPTIONS_ID'];
        $this->arResult['CATEGORIES'] = $this->getCategories(); // нужно для шаблона, но и для AJAX может пригодиться

        // Теперь обрабатываем AJAX
        if ($this->request->isAjaxRequest() && $this->request->getPost('action') === 'saveTest') {
            $this->handleSaveTest();
            return;
        }

        // Если не AJAX - просто показываем шаблон
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

        // Диагностика: выведем в лог значения ID
        AddMessage2Log("testcreator: handleSaveTest, IBLOCK_CATEGORIES_ID = " . $this->arResult['IBLOCK_CATEGORIES_ID'], "testcreator");
        AddMessage2Log("testcreator: POST = " . print_r($post, true), "testcreator");

        $testName = trim($post['testName'] ?? '');
        if (empty($testName)) {
            $this->sendJsonError('Введите название теста');
        }

        $categoryId = (int)($post['categoryId'] ?? 0);
        if (!$categoryId) {
            $this->sendJsonError('Выберите категорию');
        }

        $questionsData = json_decode($post['questionsData'] ?? '', true);
        if (empty($questionsData)) {
            $this->sendJsonError('Нет вопросов');
        }

        // Проверка ID инфоблоков (теперь они должны быть)
        $catIblockId = $this->arResult['IBLOCK_CATEGORIES_ID'];
        if (!$catIblockId) {
            $this->sendJsonError('Не задан ID инфоблока категорий (IBLOCK_CATEGORIES_ID). Полученные arParams: ' . print_r($this->arParams, true));
        }

        $testsIblockId = $this->arResult['IBLOCK_TESTS_ID'];
        if (!$testsIblockId) {
            $this->sendJsonError('Не задан ID инфоблока тестов (IBLOCK_TESTS_ID)');
        }

        $questionsIblockId = $this->arResult['IBLOCK_QUESTIONS_ID'];
        if (!$questionsIblockId) {
            $this->sendJsonError('Не задан ID инфоблока вопросов (IBLOCK_QUESTIONS_ID)');
        }

        $optionsIblockId = $this->arResult['IBLOCK_OPTIONS_ID'];
        if (!$optionsIblockId) {
            $this->sendJsonError('Не задан ID инфоблока вариантов (IBLOCK_OPTIONS_ID)');
        }

        // Существование инфоблоков
        if (!CIBlock::GetByID($catIblockId)->Fetch()) {
            $this->sendJsonError("Инфоблок категорий с ID {$catIblockId} не существует");
        }
        if (!CIBlock::GetByID($testsIblockId)->Fetch()) {
            $this->sendJsonError("Инфоблок тестов с ID {$testsIblockId} не существует");
        }
        if (!CIBlock::GetByID($questionsIblockId)->Fetch()) {
            $this->sendJsonError("Инфоблок вопросов с ID {$questionsIblockId} не существует");
        }
        if (!CIBlock::GetByID($optionsIblockId)->Fetch()) {
            $this->sendJsonError("Инфоблок вариантов с ID {$optionsIblockId} не существует");
        }

        // Проверка категории
        $categoryExists = CIBlockElement::GetByID($categoryId)->Fetch();
        if (!$categoryExists) {
            $this->sendJsonError("Категория с ID {$categoryId} не найдена. Создайте хотя бы одну категорию в инфоблоке ID={$catIblockId}");
        }

        // Права
        $rights = CIBlock::GetPermission($testsIblockId);
        if ($rights < 'W') {
            $this->sendJsonError("Недостаточно прав для добавления теста (требуется запись). Ваши права: {$rights}");
        }

        // Создание теста
        $testId = $this->createTest($testName, $post['description'] ?? '', $categoryId);
        if (!$testId) {
            $errorMsg = $this->errors->current() ? $this->errors->current()->getMessage() : 'Неизвестная ошибка';
            $this->sendJsonError('Ошибка создания теста: ' . $errorMsg);
        }

        // Создание вопросов и вариантов
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

    protected function createTest($name, $description, $categoryId)
    {
        $iblockId = $this->arResult['IBLOCK_TESTS_ID'];
        $el = new CIBlockElement();

        // Генерация символьного кода транслитом
        $code = \CUtil::translit($name, "ru", ["replace_space" => "-", "replace_other" => "-"]);
        // Проверка уникальности кода
        $i = 1;
        $originalCode = $code;
        while (\CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch()) {
            $code = $originalCode . '-' . $i++;
        }

        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'CODE' => $code,
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => $description,
            'PROPERTY_VALUES' => [
                'CATEGORY' => $categoryId,
                'DESCRIPTION' => $description,
            ],
        ];
        $testId = $el->Add($fields);
        if (!$testId) {
            $this->errors[] = new Error($el->LAST_ERROR);
            AddMessage2Log("testcreator: createTest error: " . $el->LAST_ERROR, "testcreator");
            return false;
        }
        return $testId;
    }

    protected function createQuestion($testId, $questionData)
    {
        $iblockId = $this->arResult['IBLOCK_QUESTIONS_ID'];
        $el = new CIBlockElement();
        $imageField = [];
        if (!empty($questionData['image']) && strpos($questionData['image'], 'data:image') === 0) {
            $imageFile = $this->base64ToFile($questionData['image'], 'question_image');
            if ($imageFile) {
                $imageField = $imageFile;
            }
        }
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $questionData['text'],
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => [
                'TEST_ID' => $testId,
                'QUESTION_TYPE' => $questionData['type'],
            ],
        ];
        if (!empty($imageField)) {
            $fields['PREVIEW_PICTURE'] = $imageField;
        }
        $questionId = $el->Add($fields);
        if (!$questionId) {
            $this->errors[] = new Error($el->LAST_ERROR);
            AddMessage2Log("testcreator: createQuestion error: " . $el->LAST_ERROR, "testcreator");
            return false;
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
            AddMessage2Log("testcreator: createOption error: " . $el->LAST_ERROR, "testcreator");
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
        if (!$decoded) return false;

        $tempPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $prefix . '_' . md5(uniqid()) . '.' . $ext;
        if (!is_dir(dirname($tempPath))) mkdir(dirname($tempPath), 0777, true);
        file_put_contents($tempPath, $decoded);

        $arFile = CFile::MakeFileArray($tempPath);
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