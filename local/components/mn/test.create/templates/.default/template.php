<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

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

    <div class="testcreator__info">
        <input type="text" id="testName" placeholder="Название теста" class="testcreator__test-name">
        <textarea id="testDescription" placeholder="Описание теста"></textarea>
        <textarea id="testInstruction" placeholder="Инструкция"></textarea>
        <textarea id="testPreviewText" placeholder="Анонс (краткое описание)"></textarea>

        <!-- Блок загрузки изображения анонса -->
        <div class="testcreator__info-zone">
            <div class="testcreator__image-preview-container" id="previewImageContainer">
                <div class="testcreator__image-placeholder" id="previewImagePlaceholder">
                    <i class="fas fa-image"></i>
                </div>
                <img id="previewImagePreview" class="testcreator__image-preview" style="display:none;" alt="">
            </div>
            <div class="testcreator__image-buttons">
                <button type="button" class="testcreator__image-btn" id="uploadPreviewImageBtn">
                    <i class="fas fa-upload"></i> Загрузить
                </button>
                <button type="button" class="testcreator__image-btn" id="removePreviewImageBtn" style="display:none;">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
            <input type="file" id="previewImageFileInput" accept="image/*" style="display:none;">
        </div>

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

<script>
    // Инициализация загрузки фото анонса (аналогично загрузке для вопросов, но без привязки к вопросу)
    (function() {
        const fileInput = document.getElementById('previewImageFileInput');
        const uploadBtn = document.getElementById('uploadPreviewImageBtn');
        const removeBtn = document.getElementById('removePreviewImageBtn');
        const placeholder = document.getElementById('previewImagePlaceholder');
        const previewImg = document.getElementById('previewImagePreview');
        let previewBase64 = null; // Будем хранить текущее изображение в base64

        uploadBtn.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewBase64 = ev.target.result;
                previewImg.src = previewBase64;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
                removeBtn.style.display = 'inline-flex';
            };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', function() {
            previewBase64 = null;
            previewImg.src = '';
            previewImg.style.display = 'none';
            placeholder.style.display = 'flex';
            removeBtn.style.display = 'none';
            fileInput.value = '';
        });

        // Функция для получения base64 изображения анонса при сохранении
        window.getPreviewImageBase64 = function() {
            return previewBase64;
        };
    })();
</script>