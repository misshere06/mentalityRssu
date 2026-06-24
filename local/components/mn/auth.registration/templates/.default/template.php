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
                <!-- Текстовые поля без изменений -->
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

                <!-- Кастомные селекты -->
                <?php
                $selects = [
                        ['name' => 'UF_CAFEDRA', 'label' => 'Кафедра', 'options' => $arResult['CAFEDRAS'], 'selected' => $_POST['UF_CAFEDRA'] ?? ''],
                        ['name' => 'UF_SPECIALNOST', 'label' => 'Специальность', 'options' => $arResult['SPECIALTIES'], 'selected' => $_POST['UF_SPECIALNOST'] ?? ''],
                        ['name' => 'UF_GROUP', 'label' => 'Группа', 'options' => $arResult['GROUPS'], 'selected' => $_POST['UF_GROUP'] ?? ''],
                        ['name' => 'UF_ROLE', 'label' => 'Роль', 'options' => $arResult['ROLES'], 'selected' => $_POST['UF_ROLE'] ?? ''],
                ];
                foreach ($selects as $select):
                    $selectId = 'select_' . $select['name'];
                    ?>
                    <div class="ui-form-row">
                        <label class="ui-form-label" for="<?= $selectId ?>"><?= htmlspecialcharsbx($select['label']) ?> <span class="req">*</span></label>
                        <div class="custom-select" data-name="<?= $select['name'] ?>">
                            <div class="custom-select__selected" tabindex="0" role="combobox" aria-expanded="false" aria-controls="<?= $selectId ?>_list">
                                Выберите
                            </div>
                            <ul class="custom-select__options" id="<?= $selectId ?>_list" role="listbox">
                                <li class="custom-select__option" data-value="">Выберите</li>
                                <?php foreach ($select['options'] as $id => $name): ?>
                                    <li class="custom-select__option <?= ($select['selected'] == $id) ? 'active' : '' ?>" data-value="<?= $id ?>">
                                        <?= htmlspecialcharsbx($name) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <select name="<?= $select['name'] ?>" class="ui-ctl-element custom-select__native" required hidden>
                                <option value="">Выберите</option>
                                <?php foreach ($select['options'] as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= ($select['selected'] == $id) ? 'selected' : '' ?>><?= htmlspecialcharsbx($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Загрузка фото -->
                <div class="ui-form-row">
                    <label class="ui-form-label">Фото профиля</label>
                    <div class="auth-photo-upload">
                        <input type="file"
                               id="profile_photo_input"
                               name="PROFILE_PHOTO"
                               accept="image/jpeg,image/png"
                               class="auth-photo-upload__input">
                        <label for="profile_photo_input" class="auth-photo-upload__label">
                            Выбрать фото
                        </label>
                        <span id="profile_photo_name" class="auth-photo-upload__filename"></span>
                        <small>JPG или PNG, до 5 МБ</small>
                    </div>
                </div>

                <div class="ui-form-row">
                    <button type="submit" class="ui-btn ui-btn-success">Зарегистрироваться</button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<script>
    (function() {
        // Кастомные селекты
        document.querySelectorAll('.custom-select').forEach(select => {
            const nativeSelect = select.querySelector('.custom-select__native');
            const selected = select.querySelector('.custom-select__selected');
            const options = select.querySelector('.custom-select__options');
            const optionItems = options.querySelectorAll('.custom-select__option');

            // Инициализация текста из сохранённого значения
            const currentValue = nativeSelect.value;
            if (currentValue) {
                const activeOption = options.querySelector(`[data-value="${currentValue}"]`);
                if (activeOption) {
                    selected.textContent = activeOption.textContent;
                    activeOption.classList.add('active');
                }
            }

            // Открытие / закрытие
            selected.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = options.style.display === 'block';
                closeAllSelects();
                if (!isOpen) {
                    options.style.display = 'block';
                    selected.setAttribute('aria-expanded', 'true');
                }
            });

            // Выбор опции
            optionItems.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const value = this.getAttribute('data-value');
                    nativeSelect.value = value;
                    selected.textContent = this.textContent;
                    optionItems.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    options.style.display = 'none';
                    selected.setAttribute('aria-expanded', 'false');
                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            // Закрытие при клике вне
            document.addEventListener('click', function() {
                options.style.display = 'none';
                selected.setAttribute('aria-expanded', 'false');
            });
        });

        function closeAllSelects() {
            document.querySelectorAll('.custom-select__options').forEach(opt => opt.style.display = 'none');
            document.querySelectorAll('.custom-select__selected').forEach(sel => sel.setAttribute('aria-expanded', 'false'));
        }

        // Отображение имени файла
        const fileInput = document.getElementById('profile_photo_input');
        const fileNameDisplay = document.getElementById('profile_photo_name');
        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                } else {
                    fileNameDisplay.textContent = '';
                }
            });
        }
    })();
</script>