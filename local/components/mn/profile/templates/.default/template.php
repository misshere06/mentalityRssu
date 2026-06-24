<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arResult
 * @var array $arParams
 */
\Bitrix\Main\UI\Extension::load('ui.alerts');
$user = $arResult['USER'];
$currentPhotoHtml = '';
if (!empty($user['PERSONAL_PHOTO'])) {
    $photoFile = CFile::GetFileArray($user['PERSONAL_PHOTO']);
    if ($photoFile) {
        $currentPhotoHtml = '<img src="'.$photoFile['SRC'].'" class="profile-avatar__img" alt="">';
    }
}
?>
<div class="main__container">
    <h1 class="main__title">Профиль пользователя</h1>

    <?php if (!empty($arResult['ERRORS'])): ?>
        <div class="ui-alert ui-alert-danger">
            <?php foreach ($arResult['ERRORS'] as $error): ?>
                <span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($arResult['SUCCESS']): ?>
        <div class="ui-alert ui-alert-success">
            Данные успешно сохранены.
        </div>
    <?php endif; ?>

    <form method="post" class="profile-form" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>

        <div class="info-card">
            <h3 class="info-card__title">Личные данные</h3>
            <div class="info-card__content">
                <div class="profile-avatar-block">
                    <div class="profile-avatar__current" id="avatarContainer">
                        <?php if ($currentPhotoHtml): ?>
                            <?= $currentPhotoHtml ?>
                        <?php else: ?>
                            <div class="profile-avatar__placeholder">Нет фото</div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-avatar__upload">
                        <label for="profile_photo_input" class="profile-avatar__label">Изменить фото</label>
                        <input type="file" id="profile_photo_input" name="PROFILE_PHOTO" accept="image/*" onchange="previewAvatar(this)" style="display:none;">
                    </div>
                </div>

                <div class="profile-field">
                    <label class="profile-field__label">Фамилия</label>
                    <input type="text" class="profile-field__input" name="LAST_NAME" maxlength="50" value="<?= htmlspecialcharsbx($user['LAST_NAME']) ?>">
                </div>

                <div class="profile-field">
                    <label class="profile-field__label">Имя</label>
                    <input type="text" class="profile-field__input" name="NAME" maxlength="50" value="<?= htmlspecialcharsbx($user['NAME']) ?>">
                </div>

                <div class="profile-field">
                    <label class="profile-field__label">Отчество</label>
                    <input type="text" class="profile-field__input" name="SECOND_NAME" maxlength="50" value="<?= htmlspecialcharsbx($user['SECOND_NAME']) ?>">
                </div>

                <div class="profile-field">
                    <label class="profile-field__label">Логин</label>
                    <div class="profile-field__value"><?= htmlspecialcharsbx($user['LOGIN']) ?></div>
                </div>

                <div class="profile-field">
                    <label class="profile-field__label">Email</label>
                    <input type="email" class="profile-field__input" name="EMAIL" maxlength="50" value="<?= htmlspecialcharsbx($user['EMAIL']) ?>">
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3 class="info-card__title">Сменить пароль</h3>
            <div class="info-card__content">
                <div class="profile-field">
                    <label class="profile-field__label">Новый пароль</label>
                    <input type="password" class="profile-field__input" name="NEW_PASSWORD" maxlength="50" value="" autocomplete="off">
                </div>
                <div class="profile-field">
                    <label class="profile-field__label">Подтверждение пароля</label>
                    <input type="password" class="profile-field__input" name="NEW_PASSWORD_CONFIRM" maxlength="50" value="" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <button type="submit" name="save" class="profile-btn profile-btn--save">Сохранить</button>
            <button type="reset" class="profile-btn profile-btn--reset">Сбросить</button>
        </div>
    </form>
</div>

<script>
    function previewAvatar(input) {
        const container = document.getElementById('avatarContainer');
        if (!container) return;
        container.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'profile-avatar__img';
                container.appendChild(img);
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'profile-avatar__placeholder';
            placeholder.textContent = 'Нет фото';
            container.appendChild(placeholder);
        }
    }
</script>