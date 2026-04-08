<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="test-editor">
    <form method="POST" id="testEditorForm" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="test_id" value="<?= $arResult['TEST_ID'] ?>">

        <div class="test-editor__header">
            <h2>Редактирование теста</h2>
            <button type="submit" class="test-editor__save-btn">Сохранить</button>
        </div>

        <div class="test-editor__main-info">
            <div class="test-editor__field">
                <label>Название теста</label>
                <input type="text" name="test_name" value="<?= htmlspecialchars($arResult['TEST']['NAME']) ?>">
            </div>
            <div class="test-editor__field">
                <label>Категория</label>
                <select name="category">
                    <?php foreach ($arResult['CATEGORIES'] as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($arResult['TEST']['CATEGORY_ID'] == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="test-editor__field">
                <label>Статус</label>
                <select name="status">
                    <?php foreach ($arResult['STATUS_LIST'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($arResult['TEST']['STATUS'] === $val) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="test-editor__field">
                <label>Дата начала публикации</label>
                <input type="date" name="date_from" value="<?= $arResult['TEST']['DATE_ACTIVE_FROM'] ? date('Y-m-d', strtotime($arResult['TEST']['DATE_ACTIVE_FROM'])) : '' ?>">
            </div>
            <div class="test-editor__field">
                <label>Дата окончания</label>
                <input type="date" name="date_to" value="<?= $arResult['TEST']['DATE_ACTIVE_TO'] ? date('Y-m-d', strtotime($arResult['TEST']['DATE_ACTIVE_TO'])) : '' ?>">
            </div>
            <div class="test-editor__field">
                <label>Описание</label>
                <textarea name="description"><?= htmlspecialchars($arResult['TEST']['DESCRIPTION']) ?></textarea>
            </div>
            <div class="test-editor__field">
                <label>Инструкция</label>
                <textarea name="instruction"><?= htmlspecialchars($arResult['TEST']['INSTRUCTION']) ?></textarea>
            </div>
        </div>

        <div class="test-editor__questions">
            <h3>Вопросы</h3>
            <div id="questionsContainer" class="test-editor__questions-list">
                <?php foreach ($arResult['QUESTIONS'] as $q): ?>

                    <div class="test-editor__question-card" data-question-id="<?= $q['ID'] ?>">
                        <div class="test-editor__question-head">
                            <input type="text" class="question-text" value="<?= htmlspecialchars($q['NAME']) ?>" placeholder="Текст вопроса">
                            <select class="question-type">
                                <option value="radio" <?= $q['TYPE'] === 'Один выбор (radio)' ? 'selected' : '' ?>>Один выбор</option>
                                <option value="checkbox" <?= $q['TYPE'] === 'Несколько выборов (checkbox)' ? 'selected' : '' ?>>Несколько выборов</option>
                                <option value="select" <?= $q['TYPE'] === 'Выпадающий список (select)' ? 'selected' : '' ?>>Выпадающий список</option>
                                <option value="text" <?= $q['TYPE'] === 'Короткий текст (text)' ? 'selected' : '' ?>>Короткий текст</option>
                                <option value="textarea" <?= $q['TYPE'] === 'Длинный текст (textarea)' ? 'selected' : '' ?>>Длинный текст</option>
                            </select>
                            <button type="button" class="duplicate-question-btn" title="Копировать вопрос"><i class="fas fa-copy"></i></button>
                            <button type="button" class="delete-question-btn"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="test-editor__image-zone">
                            <div class="image-preview">
                                <?php if ($q['IMAGE']): ?>
                                    <img src="<?= CFile::GetPath($q['IMAGE']) ?>" width="70">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="upload-image-btn">Загрузить</button>
                            <input type="file" class="image-file-input" accept="image/*" style="display:none">
                        </div>
                        <div class="test-editor__options-area">
                            <div class="options-list">
                                <?php foreach ($q['ANSWERS'] as $a): ?>
                                    <div class="option-item" data-answer-id="<?= $a['ID'] ?>">
                                        <input type="text" class="option-text" value="<?= htmlspecialchars($a['TEXT']) ?>" placeholder="Вариант">
                                        <input type="number" class="option-score" value="<?= $a['SCORE'] ?>" placeholder="Баллы">
                                        <button type="button" class="remove-option"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="add-option-btn">+ Добавить вариант</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="addQuestionBtn" class="test-editor__add-question">+ Добавить вопрос</button>
        </div>
    </form>
</div>