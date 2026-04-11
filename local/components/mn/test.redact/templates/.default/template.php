<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="test-redactor">
    <div class="test-redactor__header">
        <h1 class="test-redactor__title">Управление тестами</h1>
        <a href="/sozdat-test/" class="test-redactor__create-btn">Создать тест</a>
    </div>

    <form method="GET" class="test-redactor__filter">
        <input type="text" name="search" placeholder="Поиск по названию" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select name="status">
            <option value="">Все статусы</option>
            <?php foreach ($arResult['STATUS_LIST'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($_GET['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select name="category">
            <option value="">Все категории</option>
            <?php foreach ($arResult['CATEGORIES'] as $id => $name): ?>
                <option value="<?= $id ?>" <?= ($_GET['category'] ?? 0) == $id ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        <button type="submit" class="test-redactor__filter-btn">Применить</button>
        <a href="<?= $APPLICATION->GetCurPageParam('', ['search', 'status', 'category', 'date_from', 'date_to']) ?>" class="test-redactor__reset-btn">Сбросить</a>
    </form>

    <div class="test-redactor__list">
        <table class="test-redactor__table">
            <thead>
            <tr><th>ID</th><th>Название</th><th>Категория</th><th>Статус</th><th>Дата публикации</th><th>Действия</th></tr>
            </thead>
            <tbody>
            <?php if (!empty($arResult['TESTS'])): ?>
                <?php foreach ($arResult['TESTS'] as $test): ?>
                    <tr>
                        <td><?= $test['ID'] ?></td>
                        <td><?= htmlspecialchars($test['NAME']) ?></td>
                        <td><?= htmlspecialchars($test['CATEGORY']) ?></td>
                        <td><span class="status-badge status-<?= $test['STATUS'] === 'PUBLISHED' ? 'published' : 'draft' ?>"><?= $arResult['STATUS_LIST'][$test['STATUS']] ?></span></td>
                        <td><?= $test['DATE_ACTIVE_FROM'] ? date('d.m.Y', strtotime($test['DATE_ACTIVE_FROM'])) : '—' ?></td>
                        <td>
                            <a href="<?= $APPLICATION->GetCurPageParam('action=edit&test_id=' . $test['ID'], ['action', 'test_id']) ?>">Редактировать</a>
                            &nbsp;|&nbsp;
                            <a href="<?= $APPLICATION->GetCurPageParam('action=delete&test_id=' . $test['ID'] . '&' . bitrix_sessid_get(), ['action', 'test_id']) ?>"
                               onclick="return confirm('Вы уверены, что хотите удалить тест «<?= htmlspecialchars($test['NAME']) ?>» и все его вопросы?')">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">Тесты не найдены</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    if ($arResult['NAV']->getRecordCount() > 0) {
        $APPLICATION->IncludeComponent(
                'bitrix:main.pagenavigation',
                'modern',
                array(
                        'NAV_OBJECT' => $arResult['NAV'],
                        'SEF_MODE' => 'N',
                ),
                false
        );
    }
    ?>
</div>