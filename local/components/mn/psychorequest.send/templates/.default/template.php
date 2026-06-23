<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="psycho-request-form">
    <h2>Запись к психологу</h2>

    <?php if (!empty($arResult['ERROR'])): ?>
        <div class="alert alert-danger"><?= htmlspecialcharsbx($arResult['ERROR']) ?></div>
    <?php endif; ?>

    <?php if (empty($arResult['PSYCHOLOGISTS'])): ?>
        <p>В настоящее время нет доступных психологов для записи.</p>
    <?php else: ?>
        <form method="post" action="">
            <?= bitrix_sessid_post() ?>

            <div class="form-group">
                <label for="psychologist-select">Выберите психолога:</label>
                <select name="PSYCHOLOGIST_ID" id="psychologist-select" class="form-control" required>
                    <option value="">-- Выберите психолога --</option>
                    <?php foreach ($arResult['PSYCHOLOGISTS'] as $psycho): ?>
                        <option value="<?= $psycho['ID'] ?>" <?= ($psycho['ID'] == $arResult['SELECTED_PSYCHOLOGIST_ID']) ? 'selected' : '' ?>>
                            <?= htmlspecialcharsbx($psycho['FULL_NAME']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="preferred-date">Желаемая дата и время:</label>
                <input type="datetime-local" name="PREFERRED_DATE" id="preferred-date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="reason">Причина обращения:</label>
                <textarea name="REASON" id="reason" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Отправить заявку</button>
        </form>
    <?php endif; ?>
</div>