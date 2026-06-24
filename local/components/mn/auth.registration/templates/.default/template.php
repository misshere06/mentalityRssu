<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 */
\Bitrix\Main\UI\Extension::load('ui.forms');
\Bitrix\Main\UI\Extension::load('ui.alerts');
\Bitrix\Main\UI\Extension::load('ui.buttons');
?>
<div class="custom-auth-wrapper">
    <div class="custom-auth-tabs">
        <a href="<?= $APPLICATION->GetCurPageParam('', ['mode']) ?>"
           class="custom-auth-tab <?= $arResult['MODE'] == 'login' ? 'active' : '' ?>">
            Вход
        </a>
        <a href="<?= $APPLICATION->GetCurPageParam('mode=register', ['mode']) ?>"
           class="custom-auth-tab <?= $arResult['MODE'] == 'register' ? 'active' : '' ?>">
            Регистрация
        </a>
    </div>

    <?php if (!empty($arResult['ERRORS'])): ?>
        <div class="ui-alert ui-alert-danger">
            <?php foreach ($arResult['ERRORS'] as $error): ?>
                <span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($arResult['SUCCESS']): ?>
        <div class="ui-alert ui-alert-success">
            <?= htmlspecialcharsbx($arResult['SUCCESS_MESSAGE']) ?>
        </div>
    <?php else: ?>
        <form method="post" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data" class="custom-auth-form">
            <?= bitrix_sessid_post() ?>

            <?php if ($arResult['MODE'] == 'login'): ?>
                <!-- Форма входа (без изменений) -->
                <div class="ui-form-row">
                    <label class="ui-form-label">Логин</label>
                    <input type="text" name="LOGIN" class="ui-ctl-element" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Пароль</label>
                    <input type="password" name="PASSWORD" class="ui-ctl-element" required>
                </div>
                <div class="ui-form-row">
                    <button type="submit" class="ui-btn ui-btn-success">Войти</button>
                </div>

            <?php else: ?>
                <!-- Форма регистрации с новыми полями -->
                <div class="ui-form-row">
                    <label class="ui-form-label">Логин <span class="req">*</span></label>
                    <input type="text" name="LOGIN" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['LOGIN'] ?? '') ?>" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Email <span class="req">*</span></label>
                    <input type="email" name="EMAIL" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['EMAIL'] ?? '') ?>" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Пароль <span class="req">*</span></label>
                    <input type="password" name="PASSWORD" class="ui-ctl-element" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Подтверждение пароля <span class="req">*</span></label>
                    <input type="password" name="CONFIRM_PASSWORD" class="ui-ctl-element" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Фамилия <span class="req">*</span></label>
                    <input type="text" name="LAST_NAME" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['LAST_NAME'] ?? '') ?>" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Имя <span class="req">*</span></label>
                    <input type="text" name="NAME" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['NAME'] ?? '') ?>" required>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Отчество</label>
                    <input type="text" name="SECOND_NAME" class="ui-ctl-element" value="<?= htmlspecialcharsbx($_POST['SECOND_NAME'] ?? '') ?>">
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Кафедра <span class="req">*</span></label>
                    <select name="UF_CAFEDRA" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['CAFEDRAS'] as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($_POST['UF_CAFEDRA'] ?? '') == $id ? 'selected' : '' ?>><?= htmlspecialcharsbx($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Специальность <span class="req">*</span></label>
                    <select name="UF_SPECIALNOST" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['SPECIALTIES'] as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($_POST['UF_SPECIALNOST'] ?? '') == $id ? 'selected' : '' ?>><?= htmlspecialcharsbx($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Группа <span class="req">*</span></label>
                    <select name="UF_GROUP" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['GROUPS'] as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($_POST['UF_GROUP'] ?? '') == $id ? 'selected' : '' ?>><?= htmlspecialcharsbx($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Роль <span class="req">*</span></label>
                    <select name="UF_ROLE" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['ROLES'] as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($_POST['UF_ROLE'] ?? '') == $key ? 'selected' : '' ?>><?= htmlspecialcharsbx($value) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Фото профиля</label>
                    <input type="file" name="PROFILE_PHOTO" accept="image/jpeg,image/png">
                    <small>JPG или PNG, до 5 МБ</small>
                </div>
                <div class="ui-form-row">
                    <button type="submit" class="ui-btn ui-btn-success">Зарегистрироваться</button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<style>
    .req { color: red; }
    .custom-auth-wrapper { max-width: 500px; margin: 0 auto; }
    .custom-auth-tabs { display: flex; margin-bottom: 20px; }
    .custom-auth-tab { flex: 1; text-align: center; padding: 10px; background: #f0f0f0; text-decoration: none; color: #333; border-radius: 5px 5px 0 0; }
    .custom-auth-tab.active { background: #fff; border: 1px solid #ddd; border-bottom: none; }
    .custom-auth-form .ui-form-row { margin-bottom: 15px; }
    .custom-auth-form .ui-ctl-element { width: 100%; }
</style>