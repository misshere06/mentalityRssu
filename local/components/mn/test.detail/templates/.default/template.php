<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$asset = \Bitrix\Main\Page\Asset::getInstance();
$asset->addJs('/assets/js/modules/testdetail.js');

$test = $arResult['TEST'];
$questions = $arResult['QUESTIONS'];
?>

<div class="test-detail">
    <?php if ($test['PICTURE']): ?>
        <div class="test-detail__image">
            <img src="<?=CFile::GetPath($test['PICTURE'])?>" alt="<?=htmlspecialcharsbx($test['NAME'])?>">
        </div>
    <?php endif; ?>

    <h1 class="test-detail__title"><?=htmlspecialcharsbx($test['NAME'])?></h1>

    <?php if (!empty($test['CATEGORY_NAME'])): ?>
        <div class="test-detail__category">
            <span class="test-detail__category-label">Категория:</span>
            <span class="test-detail__category-value"><?=htmlspecialcharsbx($test['CATEGORY_NAME'])?></span>
        </div>
    <?php endif; ?>

    <?php
    $description = $test['PROPERTY_DESCRIPTION_VALUE'] ?: $test['PREVIEW_TEXT'];
    if ($description):
        ?>
        <div class="test-detail__desc"><?=htmlspecialcharsbx($description)?></div>
    <?php endif; ?>

    <?php if (!empty($test['PROPERTY_INSTRUCTION_VALUE'])): ?>
        <div class="test-detail__instruction">
            <div class="test-detail__instruction-title">Инструкция:</div>
            <div class="test-detail__instruction-text"><?=htmlspecialcharsbx($test['PROPERTY_INSTRUCTION_VALUE'])?></div>
        </div>
    <?php endif; ?>

    <div class="test-detail__questions">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="test-detail__question" data-question-id="<?=$q['ID']?>">
                <div class="test-detail__question-header">
                    <span class="test-detail__question-num"><?=($idx+1)?>.</span>
                    <div class="test-detail__question-text"><?=htmlspecialcharsbx($q['NAME'])?></div>
                </div>

                <?php if ($q['IMAGE_PATH']): ?>
                    <div class="test-detail__question-image">
                        <img src="<?=$q['IMAGE_PATH']?>" alt="illustration">
                    </div>
                <?php endif; ?>

                <div class="test-detail__options" data-type="<?=$q['PROPERTY_QUESTION_TYPE_VALUE']?>">
                    <?php
                    // Нормализация типа вопроса
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