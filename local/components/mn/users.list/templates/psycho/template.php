<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\UI\Extension::load("ui.alerts");

$signedParameters = $this->getComponent()->getSignedParameters();
$componentPath = $this->getComponent()->getPath();
$bookingUrlTemplate = $arParams['BOOKING_URL_TEMPLATE'] ?? '/psycho/booking/#USER_ID#/';
?>

<div class="psycho-list-container"
     data-signed-parameters="<?= htmlspecialcharsbx($signedParameters) ?>"
     data-component-path="<?= htmlspecialcharsbx($componentPath) ?>">

    <?php if (empty($arResult['USERS'])): ?>
        <div class="ui-alert ui-alert-warning">Психологи не найдены</div>
    <?php else: ?>
        <h1 class="psycho-list__title">Наши психологи</h1>
        <div class="psycho-list__grid">
            <?php foreach ($arResult['USERS'] as $user): ?>
                <div class="psycho-card">
                    <div class="psycho-card__photo">
                        <img src="<?= htmlspecialcharsbx($user['PHOTO']) ?>" alt="<?= htmlspecialcharsbx($user['FULL_NAME']) ?>">
                    </div>
                    <div class="psycho-card__info">
                        <h3 class="psycho-card__name"><?= htmlspecialcharsbx($user['FULL_NAME']) ?></h3>
                        <?php if (!empty($user['SPECIALIZATION'])): ?>
                            <p class="psycho-card__spec"><?= htmlspecialcharsbx($user['SPECIALIZATION']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($user['EXPERIENCE'])): ?>
                            <p class="psycho-card__exp">Стаж: <?= htmlspecialcharsbx($user['EXPERIENCE']) ?></p>
                        <?php endif; ?>
                        <button class="psycho-card__detail-btn"
                                data-name="<?= htmlspecialcharsbx($user['FULL_NAME']) ?>"
                                data-photo="<?= htmlspecialcharsbx($user['PHOTO']) ?>"
                                data-about="<?= htmlspecialcharsbx($user['ABOUT']) ?>"
                                data-experience="<?= htmlspecialcharsbx($user['EXPERIENCE']) ?>"
                                data-accept="<?= $user['ACCEPT_REQUESTS'] == 1 ? '1' : '0' ?>"
                                data-booking-url="<?= htmlspecialcharsbx(str_replace('#USER_ID#', $user['ID'], $bookingUrlTemplate)) ?>">
                            Подробнее
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($arResult['NAV_OBJECT']->getPageCount() > 1): ?>
            <div class="pagination">
                <?= $arResult['NAV_OBJECT']->getPages() ?>
            </div>
        <?php endif; ?>

        <!-- Модальное окно подробностей -->
        <div class="psycho-modal" id="psychoModal" style="display: none;">
            <div class="psycho-modal__overlay"></div>
            <div class="psycho-modal__content">
                <button class="psycho-modal__close">&times;</button>
                <div class="psycho-modal__body">
                    <div class="psycho-modal__photo">
                        <img id="psychoModalPhoto" src="" alt="">
                    </div>
                    <h3 id="psychoModalName"></h3>
                    <div id="psychoModalAbout" class="psycho-modal__about"></div>
                    <div id="psychoModalExp" class="psycho-modal__exp"></div>
                    <div class="psycho-modal__actions">
                        <a href="#" id="psychoModalBookBtn" class="psycho-modal__book-btn">Записаться</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>