
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
        <a href="<?= $APPLICATION->GetCurPageParam('', ['mode']) ?>" class="custom-auth-tab <?= $arResult['MODE'] == 'login' ? 'active' : '' ?>">Вход</a>
        <a href="<?= $APPLICATION->GetCurPageParam('mode=register', ['mode']) ?>" class="custom-auth-tab <?= $arResult['MODE'] == 'register' ? 'active' : '' ?>">Регистрация</a>
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
            <?= GetMessage('CUSTOM_AUTH_REGISTER_SUCCESS') ?>
        </div>
    <?php else: ?>
        <form method="post" action="<?= POST_FORM_ACTION_URI ?>" class="custom-auth-form">
            <?= bitrix_sessid_post() ?>

            <?php if ($arResult['MODE'] == 'login'): ?>
                <!-- Форма авторизации -->
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
                <!-- Форма регистрации -->
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
                    <label class="ui-form-label">Группа <span class="req">*</span></label>
                    <select name="UF_GROUP" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['GROUPS'] as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($_POST['UF_GROUP'] ?? '') == $key ? 'selected' : '' ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Специальность <span class="req">*</span></label>
                    <select name="UF_SPECIALNOST" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['SPECIALTIES'] as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($_POST['UF_SPECIALNOST'] ?? '') == $key ? 'selected' : '' ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <label class="ui-form-label">Роль <span class="req">*</span></label>
                    <select name="UF_ROLE" class="ui-ctl-element" required>
                        <option value="">Выберите</option>
                        <?php foreach ($arResult['ROLES'] as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($_POST['UF_ROLE'] ?? '') == $key ? 'selected' : '' ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ui-form-row">
                    <button type="submit" class="ui-btn ui-btn-success">Зарегистрироваться</button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<script>
    BX.ready(function() {
        <?php if (isset($_SESSION['CUSTOM_REGISTER_SUCCESS'])): ?>
            <?php $sessionData = $_SESSION['CUSTOM_REGISTER_SUCCESS']; unset($_SESSION['CUSTOM_REGISTER_SUCCESS']); ?>
            <?php if ($sessionData['ACTIVE'] === 'N'): ?>
                // Показываем попап с сообщением о необходимости активации
                BX.UI.Dialogs.MessageBox.alert(
                    '<?= CUtil::JSEscape($sessionData['MESSAGE']) ?>',
                    'Регистрация'
                );
            <?php else: ?>
                // Автоматически авторизуем или просто показываем сообщение
                BX.UI.Dialogs.MessageBox.alert(
                    'Регистрация прошла успешно! Теперь вы можете войти.',
                    'Регистрация',
                    function() {
                        window.location.href = '<?= $APPLICATION->GetCurPageParam('', ['mode']) ?>';
                    }
                );
            <?php endif; ?>
        <?php endif; ?>
    });
</script>