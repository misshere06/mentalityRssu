<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

ShowError($arResult["strProfileError"]);
if (isset($arResult['DATA_SAVED']) && $arResult['DATA_SAVED'] == 'Y')
    ShowNote(GetMessage('PROFILE_DATA_SAVED'));

// Текущее фото
$currentPhotoHtml = '';
if (!empty($arResult["arUser"]["PERSONAL_PHOTO"])) {
    $photoFile = CFile::GetFileArray($arResult["arUser"]["PERSONAL_PHOTO"]);
    if ($photoFile) {
        $currentPhotoHtml = '<img src="'.$photoFile['SRC'].'" class="profile-avatar__img" alt="">';
    }
}
?>

<div class="main__container">
    <h1 class="main__title">Профиль пользователя</h1>

    <form method="post" class="profile-form" action="<?=$arResult["FORM_TARGET"]?>" enctype="multipart/form-data">
        <?=$arResult["BX_SESSION_CHECK"]?>
        <input type="hidden" name="lang" value="<?=LANG?>" />
        <input type="hidden" name="ID" value="<?=$arResult["ID"]?>" />

        <div class="info-card">
            <h3 class="info-card__title">Личные данные</h3>
            <div class="info-card__content">
                <!-- Аватар -->
                <div class="profile-avatar-block">
                    <div class="profile-avatar__current" id="avatarContainer">
                        <?php if ($currentPhotoHtml): ?>
                            <?=$currentPhotoHtml?>
                        <?php else: ?>
                            <div class="profile-avatar__placeholder">Нет фото</div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-avatar__upload">
                        <label for="profile_photo_input" class="profile-avatar__label">Изменить фото</label>
                        <input type="file" id="profile_photo_input" name="PERSONAL_PHOTO" accept="image/*" onchange="previewAvatar(this)" style="display:none;">
                    </div>
                </div>

                <!-- Фамилия -->
                <div class="profile-field">
                    <label class="profile-field__label"><?=GetMessage('LAST_NAME')?></label>
                    <input type="text" class="profile-field__input" name="LAST_NAME" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["LAST_NAME"])?>">
                </div>

                <!-- Имя -->
                <div class="profile-field">
                    <label class="profile-field__label"><?=GetMessage('NAME')?></label>
                    <input type="text" class="profile-field__input" name="NAME" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["NAME"])?>">
                </div>

                <!-- Отчество -->
                <div class="profile-field">
                    <label class="profile-field__label"><?=GetMessage('SECOND_NAME')?></label>
                    <input type="text" class="profile-field__input" name="SECOND_NAME" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["SECOND_NAME"])?>">
                </div>

                <!-- Логин (только чтение) -->
                <div class="profile-field">
                    <label class="profile-field__label"><?=GetMessage('LOGIN')?></label>
                    <div class="profile-field__value"><?=htmlspecialcharsbx($arResult["arUser"]["LOGIN"])?></div>
                </div>

                <!-- Email -->
                <div class="profile-field">
                    <label class="profile-field__label"><?=GetMessage('EMAIL')?></label>
                    <input type="email" class="profile-field__input" name="EMAIL" maxlength="50" value="<?=htmlspecialcharsbx($arResult["arUser"]["EMAIL"])?>">
                </div>
            </div>
        </div>

        <!-- Смена пароля -->
        <?php if ($arResult['CAN_EDIT_PASSWORD']): ?>
            <div class="info-card">
                <h3 class="info-card__title">Сменить пароль</h3>
                <div class="info-card__content">
                    <div class="profile-field">
                        <label class="profile-field__label"><?=GetMessage('NEW_PASSWORD_REQ')?></label>
                        <input type="password" class="profile-field__input" name="NEW_PASSWORD" maxlength="50" value="" autocomplete="off">
                    </div>
                    <div class="profile-field">
                        <label class="profile-field__label"><?=GetMessage('NEW_PASSWORD_CONFIRM')?></label>
                        <input type="password" class="profile-field__input" name="NEW_PASSWORD_CONFIRM" maxlength="50" value="" autocomplete="off">
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Кнопки -->
        <div class="profile-actions">
            <button type="submit" name="save" class="profile-btn profile-btn--save"><?=GetMessage("MAIN_SAVE")?></button>
            <button type="reset" class="profile-btn profile-btn--reset"><?=GetMessage("MAIN_RESET")?></button>
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