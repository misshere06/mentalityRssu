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
                <div class="test-detail__question-header">
                    <span class="test-detail__question-num"><?=($idx+1)?>.</span>
                    <div class="test-detail__question-text"><?=htmlspecialcharsbx($q['NAME'])?></div>
                </div>
                <?php if ($q['PREVIEW_PICTURE']): ?>
                    <div class="test-detail__question-image">
                        <img src="<?=CFile::GetPath($q['PREVIEW_PICTURE'])?>" alt="illustration">
                    </div>
                <?php endif; ?>

                <div class="test-detail__options" data-type="<?=$q['PROPERTY_QUESTION_TYPE_VALUE']?>">
                    <?php
                    // Приводим тип к строковому идентификатору (если у вас в PROPERTY_QUESTION_TYPE_VALUE лежит ID или русское название)
                    // Рекомендую нормализовать: используйте XML_ID как в компоненте создания. Для демо предполагаем, что значение равно 'radio','checkbox','select','text','textarea'
                    $type = $q['PROPERTY_QUESTION_TYPE_VALUE'];
                    if ($type == 'Один выбор (radio)') $type = 'radio';
                    elseif ($type == 'Несколько выборов (checkbox)') $type = 'checkbox';
                    elseif ($type == 'Выпадающий список (select)') $type = 'select';
                    elseif ($type == 'Короткий текст (text)') $type = 'text';
                    elseif ($type == 'Длинный текст (textarea)') $type = 'textarea';

                    switch ($type):
                        case 'radio':
                            foreach ($q['OPTIONS'] as $opt):
                                ?>
                                <label class="test-detail__option">
                                    <input type="radio" name="question_<?=$q['ID']?>" value="<?=$opt['ID']?>">
                                    <span class="test-detail__option-text"><?=htmlspecialcharsbx($opt['NAME'])?></span>
                                </label>
                            <?php endforeach;
                            break;
                        case 'checkbox':
                            foreach ($q['OPTIONS'] as $opt):
                                ?>
                                <label class="test-detail__option test-detail__option--checkbox">
                                    <input type="checkbox" name="question_<?=$q['ID']?>[]" value="<?=$opt['ID']?>">
                                    <span class="test-detail__option-text"><?=htmlspecialcharsbx($opt['NAME'])?></span>
                                </label>
                            <?php endforeach;
                            break;
                        case 'select':
                            ?>
                            <select name="question_<?=$q['ID']?>" class="test-detail__select">
                                <option value="">-- Выберите вариант --</option>
                                <?php foreach ($q['OPTIONS'] as $opt): ?>
                                    <option value="<?=$opt['ID']?>"><?=htmlspecialcharsbx($opt['NAME'])?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php
                            break;
                        case 'text':
                            ?>
                            <input type="text" name="question_<?=$q['ID']?>" class="test-detail__text-input" placeholder="Ваш ответ">
                            <?php
                            break;
                        case 'textarea':
                            ?>
                            <textarea name="question_<?=$q['ID']?>" class="test-detail__textarea" rows="3" placeholder="Ваш ответ"></textarea>
                            <?php
                            break;
                    endswitch;
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="test-detail__actions">
        <button class="test-detail__submit" id="submitTestBtn">Отправить ответы</button>
    </div>
</div>