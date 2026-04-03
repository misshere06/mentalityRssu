<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->IncludeLangFile(__FILE__);

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;
use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");



// Получаем текущего пользователя
$currentUser = CurrentUser::get();
$userId = $currentUser->getId();

if ($userId <= 0) {
    // Если не авторизован – ничего не выводим
    return;
}

// Загружаем данные пользователя
$userData = UserTable::getList([
        'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'WORK_POSITION', 'UF_ROLE'],
        'filter' => ['=ID' => $userId],
        'limit' => 1
])->fetch();

if (!$userData) {
    return;
}

// Формируем имя
$userName = trim($userData['LAST_NAME'] . ' ' . $userData['NAME'] . ($userData['SECOND_NAME'] ? ' ' . $userData['SECOND_NAME'] : ''));
if (empty($userName)) {
    $userName = $userData['LOGIN'];
}
$userName = htmlspecialcharsbx($userName);

// Роль: приоритет UF_ROLE, затем WORK_POSITION
$role = '';
if (!empty($userData['UF_ROLE'])) {
    $roleId = (int)$userData['UF_ROLE'];

    $obEnum = new \CUserFieldEnum();
    $resEnum = $obEnum->GetList([], ['ID' => $roleId]);
    if ($arEnum = $resEnum->Fetch()) {
        $role = htmlspecialcharsbx($arEnum['VALUE']);
    }
}
if (empty($role) && !empty($userData['WORK_POSITION'])) {
    $role = htmlspecialcharsbx($userData['WORK_POSITION']);
}

// Аватар
$avatarHtml = '';
if (!empty($userData['PERSONAL_PHOTO'])) {
    $file = CFile::ResizeImageGet($userData['PERSONAL_PHOTO'], ['width' => 125, 'height' => 125], BX_RESIZE_IMAGE_EXACT);
    if ($file) {
        $avatarHtml = '<img class="user-info__avatar-img" src="' . $file['src'] . '" alt="">';
    }
}
if (empty($avatarHtml)) {
    $avatarHtml = '<img class="user-info__avatar-img" src="/assets/img/avatar.png" alt="">';
}

// Ссылка на профиль
$profileUrl = SITE_DIR . 'company/personal/user/' . $userId . '/';
$canViewProfile = true;

// Атрибуты для тултипа
$anchor_id = $this->randString(8);
$tooltipAttr = '';
if ($canViewProfile && (!isset($arResult['USE_TOOLTIP']) || $arResult['USE_TOOLTIP'])) {
    $tooltipAttr = ' bx-tooltip-user-id="' . $userId . '"';
}
$anchorIdAttr = ' id="anchor_' . $anchor_id . '"';

// Показывать кнопки действий?
$showActions = (isset($arParams['SHOW_ACTIONS']) && $arParams['SHOW_ACTIONS'] === 'Y');

// URL для кнопок
$messagesUrl = SITE_DIR . 'company/personal/messages/';
$notificationsUrl = SITE_DIR . 'company/personal/';
$profileEditUrl = $profileUrl;
$logoutUrl = SITE_DIR . '?logout=yes';
?>

<div class="sidebar__user-info">
    <div class="user-info">
        <div class="user-info__avatar">
            <a href="<?= $profileUrl ?>"<?= $anchorIdAttr . $tooltipAttr ?>>
                <?= $avatarHtml ?>
            </a>
        </div>
        <div class="user-info__details">
            <h3 class="user-info__name">
                <a href="<?= $profileUrl ?>"<?= $anchorIdAttr . $tooltipAttr ?>>
                    <?= $userName ?>
                </a>
            </h3>
            <?php if ($role): ?>
                <p class="user-info__role"><?= $role ?></p>
            <?php endif; ?>
        </div>



        <?php if ($showActions): ?>
            <div class="user-info__actions">
                <a href="<?= $messagesUrl ?>" class="action-btn"
                   aria-label="<?= GetMessage('SIDEBAR_USER_ARIA_TESTS') ?>"
                   data-tooltip="<?= GetMessage('SIDEBAR_USER_TOOLTIP_TESTS') ?>">
            <span class="action-btn__icon">
                <img src="/assets/img/svg/icon-tests.svg" alt="">
            </span>
                </a>
                <a href="<?= $notificationsUrl ?>" class="action-btn"
                   aria-label="<?= GetMessage('SIDEBAR_USER_ARIA_NOTIFICATIONS') ?>"
                   data-tooltip="<?= GetMessage('SIDEBAR_USER_TOOLTIP_NOTIFICATIONS') ?>">
            <span class="action-btn__icon">
                <img src="/assets/img/svg/icon-alerts.svg" alt="">
            </span>
                </a>
                <a href="<?= $profileEditUrl ?>" class="action-btn"
                   aria-label="<?= GetMessage('SIDEBAR_USER_ARIA_PROFILE') ?>"
                   data-tooltip="<?= GetMessage('SIDEBAR_USER_TOOLTIP_PROFILE') ?>">
            <span class="action-btn__icon">
                <img src="/assets/img/svg/icon-profile.svg" alt="">
            </span>
                </a>
                <a href="<?= $logoutUrl ?>" class="action-btn"
                   aria-label="<?= GetMessage('SIDEBAR_USER_ARIA_LOGOUT') ?>"
                   data-tooltip="<?= GetMessage('SIDEBAR_USER_TOOLTIP_LOGOUT') ?>">
            <span class="action-btn__icon">
                <img src="/assets/img/svg/icon-exit.svg" alt="">
            </span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>