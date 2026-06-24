<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$filterValue = $arResult['FILTER'];
?>
<div class="test-list-cards js-test-list-cards">
    <div class="test-list-cards__header">
        <h1 class="test-list-cards__title">Список тестов</h1>
        <div class="test-list-cards__controls">
            <!-- Табы фильтрации -->
            <div class="test-list-cards__tabs">
                <a href="?filter=all" class="test-list-cards__tab <?= $filterValue === 'all' ? 'active' : '' ?>">Все</a>
                <a href="?filter=actual" class="test-list-cards__tab <?= $filterValue === 'actual' ? 'active' : '' ?>">Актуальные</a>
                <a href="?filter=completed" class="test-list-cards__tab <?= $filterValue === 'completed' ? 'active' : '' ?>">Пройденные</a>
            </div>
            <!-- Переключатель вида -->
            <div class="test-list-cards__view-toggle">
                <button class="test-list-cards__view-btn js-view-btn active" data-view="grid" title="Сетка">
                    <svg width="18" height="18" viewBox="0 0 18 18"><rect x="1" y="1" width="7" height="7" rx="1" fill="currentColor"/><rect x="10" y="1" width="7" height="7" rx="1" fill="currentColor"/><rect x="1" y="10" width="7" height="7" rx="1" fill="currentColor"/><rect x="10" y="10" width="7" height="7" rx="1" fill="currentColor"/></svg>
                </button>
                <button class="test-list-cards__view-btn js-view-btn" data-view="list" title="Список">
                    <svg width="18" height="18" viewBox="0 0 18 18"><rect x="2" y="2" width="14" height="3" rx="1" fill="currentColor"/><rect x="2" y="7" width="14" height="3" rx="1" fill="currentColor"/><rect x="2" y="12" width="14" height="3" rx="1" fill="currentColor"/></svg>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($arResult['ITEMS'])): ?>
        <div class="test-list-cards__items js-items-container">
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <article class="test-list-cards__item js-item">
                    <?php if ($item['PREVIEW_PICTURE_SRC']): ?>
                        <div class="test-list-cards__item-image">
                            <img src="<?= htmlspecialcharsbx($item['PREVIEW_PICTURE_SRC']) ?>"
                                 alt="<?= htmlspecialcharsbx($item['NAME']) ?>"
                                 loading="lazy">
                            <!-- Индикатор прохождения -->
                            <?php if ($item['IS_COMPLETED']): ?>
                                <span class="test-list-cards__item-badge test-list-cards__item-badge--completed">✓ Пройден</span>
                            <?php else: ?>
                                <span class="test-list-cards__item-badge test-list-cards__item-badge--new">Новый</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="test-list-cards__item-content">
                        <?php if (!empty($item['CATEGORY_NAME'])): ?>
                            <span class="test-list-cards__item-category"><?= htmlspecialcharsbx($item['CATEGORY_NAME']) ?></span>
                        <?php endif; ?>
                        <h2 class="test-list-cards__item-title">
                            <a href="<?= htmlspecialcharsbx($item['DETAIL_URL']) ?>">
                                <?= htmlspecialcharsbx($item['NAME']) ?>
                            </a>
                        </h2>
                        <?php if ($item['PREVIEW_TEXT']): ?>
                            <p class="test-list-cards__item-desc">
                                <?= htmlspecialcharsbx(TruncateText($item['PREVIEW_TEXT'], 150)) ?>
                            </p>
                        <?php endif; ?>
                        <button class="test-list-cards__item-link js-show-detail"
                                data-description="<?= htmlspecialcharsbx($item['DESCRIPTION']) ?>"
                                data-instruction="<?= htmlspecialcharsbx($item['INSTRUCTION']) ?>"
                                data-completed="<?= $item['IS_COMPLETED'] ? '1' : '0' ?>"
                                data-completed-date="<?= htmlspecialcharsbx($item['COMPLETED_DATE']) ?>">
                            Подробнее →
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($arResult['NAV_STRING']): ?>
            <div class="test-list-cards__nav"><?= $arResult['NAV_STRING'] ?></div>
        <?php endif; ?>

        <!-- Попап подробностей -->
        <div class="test-list-popup js-test-popup" style="display: none;">
            <div class="test-list-popup__overlay js-popup-close"></div>
            <div class="test-list-popup__content">
                <button class="test-list-popup__close js-popup-close" aria-label="Закрыть">✕</button>
                <div class="test-list-popup__body">
                    <h3 class="test-list-popup__title js-popup-title"></h3>
                    <div class="test-list-popup__section js-popup-description"></div>
                    <div class="test-list-popup__section js-popup-instruction"></div>
                    <div class="test-list-popup__section test-list-popup__status js-popup-status"></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="test-list-cards__empty">
            <p>Тесты не найдены</p>
        </div>
    <?php endif; ?>
</div>