<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

$test = $arResult['TEST'] ?? [];
$questions = $arResult['QUESTIONS'] ?? [];

foreach ($questions as &$q) {
    // Используем свойство IMAGE вместо PREVIEW_PICTURE
    $imageId = $q['PROPERTY_IMAGE_VALUE'] ?? 0;
    $q['PICTURE_PATH'] = $imageId ? CFile::GetPath($imageId) : '';
}
unset($q);

$totalQuestions = count($questions);

// Формируем данные один раз
$questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
?>
<script>
    window.onequestionData = {
        questions: <?= $questionsJson ?>
    };
    console.log('onequestionData установлено:', window.onequestionData);
</script>

<?
// Подключаем JS
$asset = Asset::getInstance();
$asset->addJs('/assets/js/modules/testdetail.js');

?>
<div class="onequestion-test"
     data-total="<?= $totalQuestions ?>"
     data-questions='<?= $questionsJson ?>'>
    <!-- остальная верстка без изменений -->
    <div class="onequestion-test__intro js-test-intro">
        <?php if (!empty($test['PREVIEW_PICTURE'])): ?>
            <div class="onequestion-test__image">
                <img src="<?= CFile::GetPath($test['PREVIEW_PICTURE']) ?>" alt="<?= htmlspecialcharsbx($test['NAME']) ?>">
            </div>
        <?php endif; ?>
        <h1 class="onequestion-test__title"><?= htmlspecialcharsbx($test['NAME']) ?></h1>
        <?php if (!empty($test['PREVIEW_TEXT'])): ?>
            <div class="onequestion-test__description"><?= htmlspecialcharsbx($test['PREVIEW_TEXT']) ?></div>
        <?php endif; ?>
        <button class="onequestion-test__start-btn js-start-btn">Приступить</button>
    </div>

    <div class="onequestion-test__quiz js-test-quiz" style="display: none;">
        <div class="onequestion-test__progress">
            <span class="onequestion-test__progress-text js-progress-text"></span>
            <div class="onequestion-test__progress-bar">
                <div class="onequestion-test__progress-fill js-progress-fill"></div>
            </div>
        </div>
        <div class="onequestion-test__question-container js-question-container"></div>
        <button class="onequestion-test__next-btn js-next-btn">Сохранить и далее</button>
    </div>

    <div class="onequestion-test__complete js-test-complete" style="display: none;">
        <div class="onequestion-test__complete-icon">✓</div>
        <h2 class="onequestion-test__complete-title">Тест пройден!</h2>
        <p class="onequestion-test__complete-text">Спасибо за уделенное время.</p>
    </div>
</div>