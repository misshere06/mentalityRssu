<?php
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\UserFieldTable;
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('iblock')) {
    die('Модуль инфоблоков не установлен');
}

if (!CModule::IncludeModule('highloadblock')) {
    echo "Модуль highloadblock не установлен. Highload-блок для результатов не будет создан.<br>";
}

// ---------------------- Функции ----------------------

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

function getIBlockIdByCode($code) {
    $res = CIBlock::GetList([], ['CODE' => $code]);
    if ($ib = $res->Fetch()) return $ib['ID'];
    return 0;
}

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
        'SITE_ID' => ['s1'], // замените при необходимости
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

function createHighloadBlock($name, $tableName, $fields = []) {
    if (!CModule::IncludeModule('highloadblock')) {
        return false;
    }

    $hlblock = HighloadBlockTable::getList(['filter' => ['NAME' => $name]])->fetch();
    if ($hlblock) {
        echo "Highload-блок {$name} уже существует, ID={$hlblock['ID']}<br>";
        return $hlblock['ID'];
    }

    $result = HighloadBlockTable::add([
        'NAME' => $name,
        'TABLE_NAME' => $tableName,
    ]);
    if (!$result->isSuccess()) {
        echo "Ошибка создания Highload-блока {$name}: " . implode(', ', $result->getErrorMessages()) . "<br>";
        return false;
    }
    $hlblockId = $result->getId();
    echo "Создан Highload-блок {$name} (ID={$hlblockId})<br>";

    $hlblock = HighloadBlockTable::getById($hlblockId)->fetch();
    $entity = HighloadBlockTable::compileEntity($hlblock);
    $entityClass = $entity->getDataClass();

    foreach ($fields as $fieldCode => $fieldData) {
        $existingField = UserFieldTable::getList([
            'filter' => [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => $fieldCode,
            ],
            'limit' => 1,
        ])->fetch();
        if ($existingField) {
            echo "&nbsp;&nbsp;Поле {$fieldCode} уже существует<br>";
            continue;
        }

        $arField = [
            'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
            'FIELD_NAME' => $fieldCode,
            'USER_TYPE_ID' => $fieldData['TYPE'],
            'XML_ID' => $fieldCode,
            'SORT' => $fieldData['SORT'] ?? 100,
            'MULTIPLE' => ($fieldData['MULTIPLE'] ?? 'N') === 'Y' ? 'Y' : 'N',
            'MANDATORY' => ($fieldData['MANDATORY'] ?? 'N') === 'Y' ? 'Y' : 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => $fieldData['SETTINGS'] ?? [],
        ];
        if (isset($fieldData['EDIT_FORM_LABEL'])) {
            $arField['EDIT_FORM_LABEL'] = $fieldData['EDIT_FORM_LABEL'];
        } else {
            $arField['EDIT_FORM_LABEL'] = ['ru' => $fieldData['NAME'] ?? $fieldCode];
        }
        if (isset($fieldData['LIST_COLUMN_LABEL'])) {
            $arField['LIST_COLUMN_LABEL'] = $fieldData['LIST_COLUMN_LABEL'];
        } else {
            $arField['LIST_COLUMN_LABEL'] = ['ru' => $fieldData['NAME'] ?? $fieldCode];
        }

        $userField = new \CUserTypeEntity();
        $fieldId = $userField->Add($arField);
        if ($fieldId) {
            echo "&nbsp;&nbsp;+ поле {$fieldCode} (ID={$fieldId})<br>";
        } else {
            echo "&nbsp;&nbsp;! ошибка добавления поля {$fieldCode}<br>";
        }
    }

    return $hlblockId;
}

// ---------------------- 1. Инфраструктура тестов ----------------------
echo "<h3>Создание структуры тестов</h3>";
createIBlockType('tests', 'Тесты');

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

// ---------------------- 2. Структура учебного заведения ----------------------
echo "<h3>Создание структуры учебного заведения</h3>";
createIBlockType('edu_structure', 'Структура учебного заведения');

$cafedrasId = createIBlock('cafedras', 'Кафедры', 'edu_structure', []);
$specialtiesId = createIBlock('specialties', 'Специальности', 'edu_structure', [
    'CAFEDRA' => [
        'NAME' => 'Кафедра',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'cafedras',
    ],
]);
$groupsId = createIBlock('groups', 'Группы', 'edu_structure', [
    'SPECIALTY' => [
        'NAME' => 'Специальность',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'specialties',
    ],
    'CAFEDRA' => [
        'NAME' => 'Кафедра',
        'TYPE' => 'E',
        'LINK_IBLOCK_CODE' => 'cafedras',
    ],
]);

// ---------------------- 3. Highload-блок результатов ----------------------
echo "<h3>Highload-блок результатов тестов</h3>";
$hlFields = [
    'UF_USER_ID' => [
        'NAME' => 'ID пользователя',
        'TYPE' => 'integer',
        'MANDATORY' => 'Y',
        'SETTINGS' => ['SIZE' => 40],
    ],
    'UF_TEST_ID' => [
        'NAME' => 'ID теста',
        'TYPE' => 'integer',
        'MANDATORY' => 'Y',
        'SETTINGS' => ['SIZE' => 40],
    ],
    'UF_CURRENT_QUESTION' => [
        'NAME' => 'Индекс текущего вопроса',
        'TYPE' => 'integer',
        'MANDATORY' => 'N',
        'SETTINGS' => ['SIZE' => 40],
    ],
    'UF_ANSWERS' => [
        'NAME' => 'Ответы (JSON)',
        'TYPE' => 'string',
        'MANDATORY' => 'N',
        'SETTINGS' => ['SIZE' => 1000, 'ROWS' => 1],
    ],
    'UF_STATUS' => [
        'NAME' => 'Статус',
        'TYPE' => 'string',
        'MANDATORY' => 'Y',
        'SETTINGS' => ['SIZE' => 20, 'ROWS' => 1],
    ],
    'UF_SCORE' => [
        'NAME' => 'Баллы',
        'TYPE' => 'double',
        'MANDATORY' => 'N',
        'SETTINGS' => ['PRECISION' => 4, 'SIZE' => 20],
    ],
    'UF_DATE_UPDATE' => [
        'NAME' => 'Дата обновления',
        'TYPE' => 'datetime',
        'MANDATORY' => 'N',
        'SETTINGS' => [
            'DEFAULT_VALUE' => ['TYPE' => 'NOW', 'VALUE' => ''],
            'USE_SECOND' => 'N',
            'USE_TIMEZONE' => 'N',
        ],
    ],
];
$hlId = createHighloadBlock('UserTestResults', 'user_test_results', $hlFields);

// ---------------------- 4. Пользовательские поля для USER ----------------------
echo "<h3>Добавление пользовательских полей</h3>";
$oUserTypeEntity = new CUserTypeEntity();

$userFields = [
    'UF_CAFEDRA' => [
        'LABEL' => 'Кафедра',
        'IBLOCK_ID' => $cafedrasId,
        'SORT' => 100,
    ],
    'UF_SPECIALNOST' => [
        'LABEL' => 'Специальность',
        'IBLOCK_ID' => $specialtiesId,
        'SORT' => 200,
    ],
    'UF_GROUP' => [
        'LABEL' => 'Группа',
        'IBLOCK_ID' => $groupsId,
        'SORT' => 300,
    ],
];

foreach ($userFields as $fieldName => $data) {
    $res = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => $fieldName]);
    if ($res->Fetch()) {
        echo "&nbsp;&nbsp;Поле пользователя {$fieldName} уже существует<br>";
        continue;
    }

    $arField = [
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => $fieldName,
        'USER_TYPE_ID' => 'iblock_element',
        'XML_ID' => $fieldName,
        'SORT' => $data['SORT'],
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => [
            'IBLOCK_ID' => $data['IBLOCK_ID'],
            'DEFAULT_VALUE' => '',
            'DISPLAY' => 'LIST',
            'LIST_HEIGHT' => 5,
        ],
        'EDIT_FORM_LABEL' => ['ru' => $data['LABEL']],
        'LIST_COLUMN_LABEL' => ['ru' => $data['LABEL']],
    ];

    $id = $oUserTypeEntity->Add($arField);
    if ($id) {
        echo "&nbsp;&nbsp;+ поле {$fieldName} (ID={$id})<br>";
    } else {
        echo "&nbsp;&nbsp;! ошибка добавления поля {$fieldName}<br>";
    }
}
// ---------------------- 5. Поля для психологов ----------------------
echo "<h3>Добавление полей для психологов</h3>";

// UF_ROLE – список (значения)
$roleFieldRes = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_ROLE']);
if ($roleFieldRes->Fetch()) {
    echo "&nbsp;&nbsp;Поле UF_ROLE уже существует<br>";
} else {
    $oUserTypeEntity->Add([
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => 'UF_ROLE',
        'USER_TYPE_ID' => 'enumeration',
        'XML_ID' => 'UF_ROLE',
        'SORT' => 400,
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => [
            'DEFAULT_VALUE' => '',
            'DISPLAY' => 'LIST',
            'LIST_HEIGHT' => 5,
        ],
        'EDIT_FORM_LABEL' => ['ru' => 'Роль'],
        'LIST_COLUMN_LABEL' => ['ru' => 'Роль'],
    ]);
    // Добавляем варианты списка
    $enum = new CUserFieldEnum();
    $enum->SetEnumValues('UF_ROLE', [
        ['VALUE' => 'Студент', 'XML_ID' => 'student', 'SORT' => 100],
        ['VALUE' => 'Преподаватель', 'XML_ID' => 'teacher', 'SORT' => 200],
        ['VALUE' => 'Психолог', 'XML_ID' => 'psycho', 'SORT' => 300],
        ['VALUE' => 'Администратор', 'XML_ID' => 'admin', 'SORT' => 400],
    ]);
    echo "&nbsp;&nbsp;+ поле UF_ROLE<br>";
}

// UF_ABOUT – текст
if (!CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_ABOUT'])->Fetch()) {
    $oUserTypeEntity->Add([
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => 'UF_ABOUT',
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'UF_ABOUT',
        'SORT' => 500,
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'N',
        'SHOW_IN_LIST' => 'N',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => ['SIZE' => 80, 'ROWS' => 5],
        'EDIT_FORM_LABEL' => ['ru' => 'О себе'],
        'LIST_COLUMN_LABEL' => ['ru' => 'О себе'],
    ]);
    echo "&nbsp;&nbsp;+ поле UF_ABOUT<br>";
} else { echo "&nbsp;&nbsp;Поле UF_ABOUT уже существует<br>"; }

// UF_EXPERIENCE – строка
if (!CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_EXPERIENCE'])->Fetch()) {
    $oUserTypeEntity->Add([
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => 'UF_EXPERIENCE',
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'UF_EXPERIENCE',
        'SORT' => 600,
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'N',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => ['SIZE' => 50],
        'EDIT_FORM_LABEL' => ['ru' => 'Стаж'],
        'LIST_COLUMN_LABEL' => ['ru' => 'Стаж'],
    ]);
    echo "&nbsp;&nbsp;+ поле UF_EXPERIENCE<br>";
} else { echo "&nbsp;&nbsp;Поле UF_EXPERIENCE уже существует<br>"; }

// UF_SPECIALIZATION – строка
if (!CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_SPECIALIZATION'])->Fetch()) {
    $oUserTypeEntity->Add([
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => 'UF_SPECIALIZATION',
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'UF_SPECIALIZATION',
        'SORT' => 700,
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'N',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => ['SIZE' => 80],
        'EDIT_FORM_LABEL' => ['ru' => 'Специализация'],
        'LIST_COLUMN_LABEL' => ['ru' => 'Специализация'],
    ]);
    echo "&nbsp;&nbsp;+ поле UF_SPECIALIZATION<br>";
} else { echo "&nbsp;&nbsp;Поле UF_SPECIALIZATION уже существует<br>"; }

// UF_ACCEPT_REQUESTS – флажок (boolean)
$fieldName = 'UF_ACCEPT_REQUESTS';
$existingField = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'FIELD_NAME' => $fieldName])->Fetch();

// Если поле есть, но не boolean — удалим его
if ($existingField && $existingField['USER_TYPE_ID'] !== 'boolean') {
    if (CUserTypeEntity::Delete($existingField['ID'])) {
        echo "&nbsp;&nbsp;Поле {$fieldName} старого типа удалено<br>";
        $existingField = false;
    } else {
        echo "&nbsp;&nbsp;! Не удалось удалить устаревшее поле {$fieldName}<br>";
    }
}

if (!$existingField) {
    $oUserTypeEntity->Add([
        'ENTITY_ID' => 'USER',
        'FIELD_NAME' => $fieldName,
        'USER_TYPE_ID' => 'boolean',       // флажок
        'XML_ID' => $fieldName,
        'SORT' => 800,
        'MULTIPLE' => 'N',
        'MANDATORY' => 'N',
        'SHOW_FILTER' => 'Y',
        'SHOW_IN_LIST' => 'Y',
        'EDIT_IN_LIST' => 'Y',
        'IS_SEARCHABLE' => 'N',
        'SETTINGS' => [
            'DEFAULT_VALUE' => 0,          // по умолчанию не отмечен (Нет)
            'DISPLAY' => 'CHECKBOX',
        ],
        'EDIT_FORM_LABEL' => ['ru' => 'Принимает заявки'],
        'LIST_COLUMN_LABEL' => ['ru' => 'Принимает заявки'],
    ]);
    echo "&nbsp;&nbsp;+ поле UF_ACCEPT_REQUESTS (boolean)<br>";
} else {
    echo "&nbsp;&nbsp;Поле UF_ACCEPT_REQUESTS уже существует в нужном типе<br>";
}
// ---------------------- 6. Заявки на запись к психологу ----------------------
echo "<h3>Создание инфоблока заявок на запись</h3>";
createIBlockType('psycho_requests', 'Заявки к психологам');

$psychoRequestsId = createIBlock('psycho_requests', 'Заявки на запись', 'psycho_requests', [
    'PSYCHOLOGIST_ID' => [
        'NAME' => 'Психолог',
        'TYPE' => 'S',
        'USER_TYPE' => 'UserID',   // привязка к пользователю
    ],
    'STUDENT_ID' => [
        'NAME' => 'Студент',
        'TYPE' => 'S',
        'USER_TYPE' => 'UserID',   // привязка к пользователю
    ],
    'STATUS' => [
        'NAME' => 'Статус заявки',
        'TYPE' => 'L',
        'VALUES' => [
            ['VALUE' => 'Новая', 'XML_ID' => 'new', 'SORT' => 100],
            ['VALUE' => 'Принята', 'XML_ID' => 'accepted', 'SORT' => 200],
            ['VALUE' => 'Завершена', 'XML_ID' => 'completed', 'SORT' => 300],
            ['VALUE' => 'Отменена', 'XML_ID' => 'cancelled', 'SORT' => 400],
        ],
    ],
    'PREFERRED_DATE' => [
        'NAME' => 'Предпочтительная дата',
        'TYPE' => 'S',
        'USER_TYPE' => 'DateTime', // поле даты со временем
    ],
    'REASON' => [
        'NAME' => 'Причина обращения',
        'TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SETTINGS' => ['SIZE' => 80, 'ROWS' => 5],
    ],
    // CREATED_AT не добавляем – используем стандартное DATE_CREATE элемента
]);
// ---------------------- Итоги ----------------------
echo "<hr><h3>Готово!</h3>";

echo "<b>Тесты:</b><br>";
echo "Категории: {$categoriesId}<br>";
echo "Тесты: {$testsId}<br>";
echo "Вопросы: {$questionsId}<br>";
echo "Варианты: {$optionsId}<br>";
echo "Highload-блок результатов: " . ($hlId ? "ID={$hlId}" : "не создан") . "<br>";

echo "<br><b>Структура учебного заведения:</b><br>";
echo "Кафедры: {$cafedrasId}<br>";
echo "Специальности: {$specialtiesId}<br>";
echo "Группы: {$groupsId}<br>";

echo "<br><b>Базовые поля пользователя:</b> UF_CAFEDRA, UF_SPECIALNOST, UF_GROUP (привязаны к соответствующим инфоблокам)<br>";

echo "<br><b>Поля для психологов (добавлены):</b><br>";
echo "UF_ROLE – список ролей (Студент, Преподаватель, Психолог, Администратор)<br>";
echo "UF_ABOUT – текст «О себе»<br>";
echo "UF_EXPERIENCE – стаж<br>";
echo "UF_SPECIALIZATION – специализация<br>";
echo "UF_ACCEPT_REQUESTS – флажок «Принимает заявки» (0 – нет, 1 – да)<br>";
echo "<br><b>Заявки к психологам:</b> {$psychoRequestsId}<br>";
echo "<br><b>Важно:</b> Создайте вручную группу «Психологи» (или используйте существующую) и добавьте в неё пользователей-психологов. Укажите ID этой группы в параметре GROUPS_IDS компонента users.list с шаблоном psycho.";

echo "<br><br>Скопируйте ID в параметры компонентов.<br>";