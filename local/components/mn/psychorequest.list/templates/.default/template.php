<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="psycho-request-list js-psycho-request-list"
     data-mode="<?= $arResult['MODE'] ?>"
     data-iblock-id="<?= (int)$arParams['IBLOCK_ID'] ?>">

    <h2>Мои заявки</h2>

    <?php if (empty($arResult['ITEMS'])): ?>
        <p>У вас пока нет заявок.</p>
    <?php else: ?>
        <!-- Десктопная таблица -->
        <div class="psycho-request-list__table-wrap">
            <table class="psycho-request-list__table">
                <thead>
                <tr>
                    <th>Психолог</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arResult['ITEMS'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialcharsbx($item['PSYCHOLOGIST_NAME']) ?></td>
                        <td><?= htmlspecialcharsbx($item['PREFERRED_DATE']) ?></td>
                        <td><?= htmlspecialcharsbx($item['STATUS']) ?></td>
                        <td><button class="btn btn-sm js-detail-btn" data-request-id="<?= $item['ID'] ?>">Подробнее</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Мобильные карточки -->
        <div class="psycho-request-list__cards">
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <div class="psycho-request-card">
                    <div class="card-row"><strong>Психолог:</strong> <?= htmlspecialcharsbx($item['PSYCHOLOGIST_NAME']) ?></div>
                    <div class="card-row"><strong>Дата:</strong> <?= htmlspecialcharsbx($item['PREFERRED_DATE']) ?></div>
                    <div class="card-row"><strong>Статус:</strong> <?= htmlspecialcharsbx($item['STATUS']) ?></div>
                    <button class="btn btn-sm js-detail-btn" data-request-id="<?= $item['ID'] ?>">Подробнее</button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($arResult['NAV_STRING']): ?>
            <div class="psycho-request-list__pagination"><?= $arResult['NAV_STRING'] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Попап -->
    <div class="request-popup js-request-popup" style="display: none;">
        <div class="request-popup__overlay js-popup-overlay"></div>
        <div class="request-popup__content">
            <button class="request-popup__close js-popup-close">&times;</button>
            <div class="request-popup__body js-popup-content"></div>
        </div>
    </div>
</div>