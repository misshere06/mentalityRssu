<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

$test = $arResult['TEST'] ?? [];
$questions = $arResult['QUESTIONS'] ?? [];

foreach ($questions as &$q) {
    $q['PICTURE_PATH'] = $q['PREVIEW_PICTURE'] ? CFile::GetPath($q['PREVIEW_PICTURE']) : '';
}
unset($q);

$totalQuestions = count($questions);

// ВАЖНО: определяем данные ДО подключения JS
$questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
?>
<script>
    window.onequestionData = {
        questions: <?= $questionsJson ?>
    };
    console.log('onequestionData установлено:', window.onequestionData);
</script>

<?
// Теперь подключаем JS (он будет использовать уже готовую переменную)
$asset = Asset::getInstance();
$asset->addJs('/assets/js/modules/testdetail.js');

?>
<div class="onequestion-test"
     data-total="<?= $totalQuestions ?>"
     data-questions='<?= json_encode($questions, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>'>
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

<script>
    console.log('Raw PHP questions:', <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>);
    window.onequestionData = {
        questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
    };
</script>