<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная");
use Bitrix\Main\Loader;
Loader::includeModule('iblock');

// ---------- 1. Количество активных тестов (инфоблок psycho_tests) ----------
$testsCount = 0;
$testsRes = CIBlockElement::GetList([], ['IBLOCK_ID' => 6, 'ACTIVE' => 'Y'], false, false, ['ID']);
if ($testsRes) {
    $testsCount = $testsRes->SelectedRowsCount();
}

// ---------- 2. Статистика по прохождению тестов (highload-блок UserTestResults) ----------
Loader::includeModule('highloadblock');
use Bitrix\Highloadblock\HighloadBlockTable;

$hlblock = HighloadBlockTable::getById(1)->fetch();
if ($hlblock) {
    $entity = HighloadBlockTable::compileEntity($hlblock);
    $entityDataClass = $entity->getDataClass();

    // Общее количество записей результатов тестов
    $totalResults = $entityDataClass::getCount();

    // Завершённые тесты (UF_STATUS = 'completed')
    $completedResults = $entityDataClass::getCount(['UF_STATUS' => 'completed']);

    // Уникальные студенты, проходившие тесты
    $studentIds = [];
    $res = $entityDataClass::getList([
            'select' => ['UF_USER_ID'],
            'group' => ['UF_USER_ID']
    ]);
    while ($row = $res->fetch()) {
        $studentIds[] = $row['UF_USER_ID'];
    }
    $activeStudents = count($studentIds);
} else {
    $totalResults = $completedResults = $activeStudents = 0;
}

// ---------- 3. Количество психологов (уникальные PSYCHOLOGIST_ID в заявках) ----------
$psychoCount = 0;
$reqRes = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 12, 'ACTIVE' => 'Y'],
        ['PROPERTY_PSYCHOLOGIST_ID'],
        false,
        ['PROPERTY_PSYCHOLOGIST_ID']
);
while ($req = $reqRes->Fetch()) {
    if (!empty($req['PROPERTY_PSYCHOLOGIST_ID_VALUE'])) {
        $psychoCount++;
    }
}

// ---------- 4. Процент завершённых тестов ----------
$completePercent = ($totalResults > 0) ? round(($completedResults / $totalResults) * 100) : 0;
?>

    <div class="main__container">
        <!-- Слайдер -->
        <section class="hero-slider">
            <div class="hero-slider__slides">
                <div class="hero-slider__slide active">
                    <img src="/assets/img/RSSU.jpg" alt="Психологический мониторинг">
                </div>
                <div class="hero-slider__slide">
                    <img src="/assets/img/RSSU2.png" alt="Кампус РГСУ">
                </div>
                <div class="hero-slider__slide">
                    <img src="/assets/img/RSSU3.png" alt="Аналитика и отчёты">
                </div>
            </div>
            <button class="hero-slider__btn hero-slider__btn--prev" aria-label="Предыдущий слайд">‹</button>
            <button class="hero-slider__btn hero-slider__btn--next" aria-label="Следующий слайд">›</button>
            <div class="hero-slider__dots"></div>
            <div class="hero-slider__caption">
                <!-- Заполнится динамически через JS -->
            </div>
        </section>

        <!-- Ключевые показатели -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-card__icon">👥</div>
                <div class="stat-card__value"><?=$activeStudents?></div>
                <div class="stat-card__label">Активных студентов</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">🧠</div>
                <div class="stat-card__value"><?=$psychoCount?></div>
                <div class="stat-card__label">Психологов-консультантов</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">📊</div>
                <div class="stat-card__value"><?=$completedResults?></div>
                <div class="stat-card__label">Пройдено тестов</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__icon">⭐</div>
                <div class="stat-card__value"><?=$completePercent?>%</div>
                <div class="stat-card__label">Завершённых тестов</div>
            </div>
        </section>

        <!-- О системе -->
        <div class="info-card">
            <h3 class="info-card__title">О системе Mentality RGSU</h3>
            <div class="info-card__content">
                <p>Платформа создана Лабораторией психологических исследований РГСУ для автоматизации сбора, обработки и анализа данных о психологическом состоянии студентов. Система позволяет проводить регулярные опросы, выявлять группы риска и предоставлять своевременную помощь.</p>
                <ul class="info-card__list">
                    <li>Автоматические опросы по расписанию</li>
                    <li>Индивидуальные рекомендации студентам</li>
                    <li>Дашборды для психологов и администраторов</li>
                    <li>Строгая конфиденциальность данных</li>
                </ul>
            </div>
        </div>

        <!-- Новости -->
        <div class="info-card">
            <h3 class="info-card__title">Новости системы</h3>
            <div class="info-card__content">
                <p><strong>15 мая 2026</strong> — Добавлен новый опросник «Стрессоустойчивость».</p>
                <p><strong>1 мая 2026</strong> — Интерфейс адаптирован для мобильных устройств.</p>
                <p><strong>20 апреля 2026</strong> — Система успешно прошла апробацию на факультете психологии.</p>
            </div>
        </div>
    </div>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>