
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

// Подключаем стили и скрипты только для этого компонента
$asset = \Bitrix\Main\Page\Asset::getInstance();
$asset->addCss($this->GetFolder().'/style.css');

if (!empty($arResult['ITEMS'])): ?>
    <div class="test-list">
        <h1 class="test-list__title">Список тестов</h1>
        <div class="test-list__items">
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <div class="test-list__item">
                    <h2 class="test-list__item-title">
                        <a href="<?=str_replace('#ELEMENT_CODE#', $item['CODE'], $arParams['DETAIL_URL'])?>">
                            <?=htmlspecialcharsbx($item['NAME'])?>
                        </a>
                        <a href="<?=$item['DETAIL_URL']?>"><?=htmlspecialcharsbx($item['NAME'])?></a>
                    </h2>
                    <?php if ($item['PREVIEW_TEXT']): ?>
                        <div class="test-list__item-desc"><?=htmlspecialcharsbx($item['PREVIEW_TEXT'])?></div>
                    <?php endif; ?>
                    <a class="test-list__item-link" href="<?=$item['DETAIL_URL']?>">Подробнее →</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($arResult['NAV_STRING']): ?>
            <div class="test-list__nav"><?=$arResult['NAV_STRING']?></div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="test-list-empty">Тесты не найдены.</div>
<?php endif; ?>