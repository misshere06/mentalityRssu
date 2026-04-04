
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$asset = \Bitrix\Main\Page\Asset::getInstance();

$asset->addJs('/assets/js/modules/testdetail.js');

$test = $arResult['TEST'];
?>
<div class="test-detail">
    <h1 class="test-detail__title"><?=htmlspecialcharsbx($test['NAME'])?></h1>
    <?php if ($test['PREVIEW_TEXT']): ?>
        <div class="test-detail__desc"><?=htmlspecialcharsbx($test['PREVIEW_TEXT'])?></div>
    <?php endif; ?>

    <div class="test-detail__questions">
        <?php foreach ($arResult['QUESTIONS'] as $idx => $q): ?>
        <div class="test-detail__question" data-question-id="<?=$q['ID']?>">
            <div class="test-detail__question-text">
                <span class="test-detail__question-num"><?=($idx+1)?>.</span>
                <?=htmlspecialcharsbx($q['NAME'])?>
            </div>
            <?php if ($q['PREVIEW_PICTURE']): ?>
                <img src="<?=CFile::GetPath($q['PREVIEW_PICTURE'])?>" class="test-detail__question-image">
            <?php endif; ?>

            <div class="test-detail__options" data-type="<?=$q['PROPERTY_QUESTION_TYPE_VALUE']?>">
                <?php if (in_array($q['PROPERTY_QUESTION_TYPE_VALUE'], ['Один выбор (radio)', 'checkbox', 'select'])): ?>
                    <?php foreach ($q['OPTIONS'] as $opt): ?>
                        <label class="test-detail__option">
                            <?php if ($q['PROPERTY_QUESTION_TYPE_VALUE'] == 'radio'): ?>
                                <input type="radio" name="question_<?=$q['ID']?>" value="<?=$opt['ID']?>">
                            <?php elseif ($q['PROPERTY_QUESTION_TYPE_VALUE'] == 'checkbox'): ?>
                                <input type="checkbox" name="question_<?=$q['ID']?>[]" value="<?=$opt['ID']?>">
                            <?php elseif ($q['PROPERTY_QUESTION_TYPE_VALUE'] == 'select'): ?>
                                <!-- выпадающий список пока не реализуем, но можно -->
                            <?php endif; ?>
                            <span><?=htmlspecialcharsbx($opt['NAME'])?></span>
                        </label>
                    <?php endforeach; ?>
                <?php elseif ($q['PROPERTY_QUESTION_TYPE_VALUE'] == 'text'): ?>
                    <input type="text" class="test-detail__text-input" placeholder="Ваш ответ">
                <?php elseif ($q['PROPERTY_QUESTION_TYPE_VALUE'] == 'textarea'): ?>
                    <textarea class="test-detail__textarea" rows="3" placeholder="Ваш ответ"></textarea>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="test-detail__actions">
        <button class="test-detail__submit" id="submitTestBtn">Отправить ответы</button>
    </div>
</div>