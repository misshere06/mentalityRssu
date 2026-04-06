<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('iblock')) {
    die('Модуль инфоблоков не установлен');
}

// Создание типа инфоблока, если его нет
function createIBlockType($typeId, $name) {
    $res = CIBlockType::GetByID($typeId);
    if ($res->Fetch()) {
        return true;
    }
    $arFields = [
        'ID' => $typeId,
        'SECTIONS' => 'Y',
        'IN_RSS' => 'N',
        'SORT' => 500,
        'LANG' => [
            'ru' => [
                'NAME' => $name,
                'SECTION_NAME' => 'Разделы',
                'ELEMENT_NAME' => 'Элементы',
            ],
            'en' => [
                'NAME' => $name,
                'SECTION_NAME' => 'Sections',
                'ELEMENT_NAME' => 'Elements',
            ],
        ],
    ];
    $ob = new CIBlockType;
    $result = $ob->Add($arFields);
    if (!$result) {
        echo "Ошибка создания типа инфоблока: " . $ob->LAST_ERROR . "<br>";
        return false;
    }
    echo "Создан тип инфоблока {$typeId}<br>";
    return true;
}

// Создаём тип 'tests'
createIBlockType('tests', 'Тесты');

// Функция создания инфоблока
function createIBlock($code, $name, $type = 'tests', $properties = []) {
    $ib = new CIBlock;
    $res = CIBlock::GetList([], ['CODE' => $code]);
    if ($iblock = $res->Fetch()) {
        echo "Инфоблок {$code} уже существует, ID={$iblock['ID']}<br>";
        return $iblock['ID'];
    }

    $arFields = [
        'ACTIVE' => 'Y',
        'NAME' => $name,
        'CODE' => $code,
        'IBLOCK_TYPE_ID' => $type,
        'SITE_ID' => ['s1'], // если сайт не s1, замените
        'GROUP_ID' => ['2' => 'R'],
        'VERSION' => 2,
        'WORKFLOW' => 'N',
        'BIZPROC' => 'N',
        'SORT' => 500,
    ];

    $id = $ib->Add($arFields);
    if (!$id) {
        echo "Ошибка создания {$code}: " . $ib->LAST_ERROR . "<br>";
        return false;
    }

    echo "Создан инфоблок {$name} (ID={$id})<br>";

    // Добавление свойств
    foreach ($properties as $propCode => $propData) {
        $prop = new CIBlockProperty;
        $propRes = CIBlockProperty::GetList([], ['IBLOCK_ID' => $id, 'CODE' => $propCode]);
        if (!$propRes->Fetch()) {
            $arProp = [
                'NAME' => $propData['NAME'],
                'ACTIVE' => 'Y',
                'SORT' => 100,
                'CODE' => $propCode,
                'PROPERTY_TYPE' => $propData['TYPE'],
                'IBLOCK_ID' => $id,
            ];
            if ($propData['TYPE'] == 'L') {
                $arProp['VALUES'] = $propData['VALUES'];
            }
            if ($propData['TYPE'] == 'E') {
                $linkedCode = $propData['LINK_IBLOCK_CODE'] ?? '';
                if ($linkedCode) {
                    $linkedId = getIBlockIdByCode($linkedCode);
                    if ($linkedId) {
                        $arProp['LINK_IBLOCK_ID'] = $linkedId;
                    } else {
                        echo "&nbsp;&nbsp;! Свойство {$propCode} пропущено: инфоблок {$linkedCode} ещё не создан<br>";
                        continue;
                    }
                }
            }
            $propId = $prop->Add($arProp);
            if ($propId) {
                echo "&nbsp;&nbsp;+ свойство {$propCode}<br>";
            } else {
                echo "&nbsp;&nbsp;! ошибка свойства {$propCode}<br>";
            }
        }
    }
    return $id;
}

function getIBlockIdByCode($code) {
    $res = CIBlock::GetList([], ['CODE' => $code]);
    if ($ib = $res->Fetch()) return $ib['ID'];
    return 0;
}

// Создание инфоблоков
$categoriesId = createIBlock('test_categories', 'Категории тестов', 'tests', []);
$testsId = createIBlock('psycho_tests', 'Психологические тесты', 'tests', [
    'CATEGORY' => [
        'NAME' => 'Категория',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'test_categories',
    ],
    'DESCRIPTION' => ['NAME' => 'Описание', 'TYPE' => 'S'],
    'INSTRUCTION' => ['NAME' => 'Инструкция', 'TYPE' => 'S'],
]);
$questionsId = createIBlock('test_questions', 'Вопросы тестов', 'tests', [
    'TEST_ID' => [
        'NAME' => 'Тест',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'psycho_tests',
    ],
    'QUESTION_TYPE' => [
        'NAME' => 'Тип вопроса',
        'TYPE' => 'L',
        'VALUES' => [
            ['VALUE' => 'Один выбор (radio)', 'XML_ID' => 'radio', 'SORT' => 100],
            ['VALUE' => 'Несколько выборов (checkbox)', 'XML_ID' => 'checkbox', 'SORT' => 200],
            ['VALUE' => 'Выпадающий список (select)', 'XML_ID' => 'select', 'SORT' => 300],
            ['VALUE' => 'Короткий текст (text)', 'XML_ID' => 'text', 'SORT' => 400],
            ['VALUE' => 'Длинный текст (textarea)', 'XML_ID' => 'textarea', 'SORT' => 500],
        ],
    ],
    'IMAGE' => ['NAME' => 'Изображение', 'TYPE' => 'F'],
]);
$optionsId = createIBlock('answer_options', 'Варианты ответов', 'tests', [
    'QUESTION_ID' => [
        'NAME' => 'Вопрос',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'test_questions',
    ],
    'SCORE' => ['NAME' => 'Баллы', 'TYPE' => 'N'],
]);

echo "<hr>Готово! ID инфоблоков:<br>";
echo "Категории: {$categoriesId}<br>";
echo "Тесты: {$testsId}<br>";
echo "Вопросы: {$questionsId}<br>";
echo "Варианты: {$optionsId}<br>";
echo "Скопируйте эти ID в параметры компонента: IBLOCK_CATEGORIES_ID, IBLOCK_TESTS_ID, IBLOCK_QUESTIONS_ID, IBLOCK_OPTIONS_ID";
?>