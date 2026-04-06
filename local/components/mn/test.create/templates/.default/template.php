<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

// Подключаем CSS и JS
CJSCore::Init(['ajax']);

?>

<div class="testcreator" data-component-path="<?=$this->GetFolder()?>">
    <div class="testcreator__header">
        <div class="testcreator__title">
            <i class="fas fa-clipboard-list"></i>
            <span>Конструктор тестов</span>
        </div>
        <button class="testcreator__add-btn" id="addQuestionBtn">
            <i class="fas fa-plus-circle"></i> Добавить вопрос
        </button>
        <button class="testcreator__create-btn" id="createTestBtn">
            <i class="fas fa-angle-double-right"></i> Создать тест
        </button>
    </div>

    <!-- Блок информации о тесте -->
    <div class="testcreator__info">
        <input type="text" id="testName" placeholder="Название теста" class="testcreator__test-name">
        <textarea id="testDescription" placeholder="Описание теста (инструкция)"></textarea>
        <select id="categoryId" class="testcreator__category-select">
            <option value="0">-- Выберите категорию --</option>
            <?php foreach ($arResult['CATEGORIES'] as $cat): ?>
                <option value="<?=$cat['ID']?>"><?=htmlspecialcharsbx($cat['NAME'])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="questionsContainer" class="testcreator__questions">
        <!-- Вопросы добавляются динамически -->
    </div>

    <div class="testcreator__footer">
        <div class="testcreator__footer-left">
            <button class="testcreator__add-btn-footer" id="addQuestionBtnFooter">
                <i class="fas fa-plus-circle"></i> Добавить вопрос
            </button>
            <span class="testcreator__question-counter" id="questionCounter">
                Вопросов: <span id="counterValue">0</span>
            </span>
        </div>
        <button class="testcreator__scroll-top-btn" id="scrollTopBtn">
            <i class="fas fa-arrow-up"></i> Наверх
        </button>
    </div>
</div>