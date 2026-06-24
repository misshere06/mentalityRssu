<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->IncludeLangFile(__FILE__);

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;
use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

$currentUser = CurrentUser::get();
$userId = $currentUser->getId();

if ($userId <= 0) {
    // Не авторизован — покажем ссылку "Войти"
    ?>
    <div class="burger-user">
        <a href="/auth/" class="burger-user__login-link">Войти</a>
    </div>
    <?php
    return;
}

$userData = UserTable::getList([
        'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'WORK_POSITION', 'UF_ROLE'],
        'filter' => ['=ID' => $userId],
        'limit' => 1
])->fetch();

if (!$userData) {
    return;
}

$userName = trim($userData['LAST_NAME'] . ' ' . $userData['NAME'] . ($userData['SECOND_NAME'] ? ' ' . $userData['SECOND_NAME'] : ''));
if (empty($userName)) {
    $userName = $userData['LOGIN'];
}
$userName = htmlspecialcharsbx($userName);

$role = '';
if (!empty($userData['UF_ROLE'])) {
    $obEnum = new CUserFieldEnum();
    $resEnum = $obEnum->GetList([], ['ID' => (int)$userData['UF_ROLE']]);
    if ($arEnum = $resEnum->Fetch()) {
        $role = htmlspecialcharsbx($arEnum['VALUE']);
    }
}
if (empty($role) && !empty($userData['WORK_POSITION'])) {
    $role = htmlspecialcharsbx($userData['WORK_POSITION']);
}

$avatarHtml = '';
if (!empty($userData['PERSONAL_PHOTO'])) {
    $file = CFile::ResizeImageGet($userData['PERSONAL_PHOTO'], ['width' => 100, 'height' => 100], BX_RESIZE_IMAGE_EXACT);
    if ($file) {
        $avatarHtml = '<img src="' . $file['src'] . '" alt="" class="burger-user__avatar-img">';
    }
}
if (empty($avatarHtml)) {
    $avatarHtml = '<img src="/assets/img/avatar.png" alt="" class="burger-user__avatar-img">';
}

$profileUrl = SITE_DIR . 'profile/';
$showActions = (isset($arParams['SHOW_ACTIONS']) && $arParams['SHOW_ACTIONS'] === 'Y');

$messagesUrl = "/tests/?filter=completed";
$notificationsUrl = "/psychology/requests";
$profileEditUrl = $profileUrl;
$logoutUrl = SITE_DIR . '?logout=yes&' . bitrix_sessid_get();
?>

<div class="burger-user">
    <div class="burger-user__header">
        <a href="<?= $profileUrl ?>" class="burger-user__avatar">
            <?= $avatarHtml ?>
        </a>
        <div class="burger-user__info">
            <a href="<?= $profileUrl ?>" class="burger-user__name"><?= $userName ?></a>
            <?php if ($role): ?>
                <p class="burger-user__role"><?= $role ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($showActions): ?>
        <div class="burger-user__actions">
            <a href="<?= $messagesUrl ?>" class="burger-user__action">
                <span class="burger-user__action-icon"><img src="/assets/img/svg/icon-tests.svg" alt=""></span>
                <span class="burger-user__action-text"><?= GetMessage('SIDEBAR_USER_TOOLTIP_TESTS') ?></span>
            </a>
            <a href="<?= $notificationsUrl ?>" class="burger-user__action">
                <span class="burger-user__action-icon"><img src="/assets/img/svg/icon-alerts.svg" alt=""></span>
                <span class="burger-user__action-text"><?= GetMessage('SIDEBAR_USER_TOOLTIP_NOTIFICATIONS') ?></span>
            </a>
            <a href="<?= $profileEditUrl ?>" class="burger-user__action">
                <span class="burger-user__action-icon"><img src="/assets/img/svg/icon-profile.svg" alt=""></span>
                <span class="burger-user__action-text"><?= GetMessage('SIDEBAR_USER_TOOLTIP_PROFILE') ?></span>
            </a>
            <a href="<?= $logoutUrl ?>" class="burger-user__action">
                <span class="burger-user__action-icon"><img src="/assets/img/svg/icon-exit.svg" alt=""></span>
                <span class="burger-user__action-text"><?= GetMessage('SIDEBAR_USER_TOOLTIP_LOGOUT') ?></span>
            </a>
        </div>
    <?php endif; ?>
</div>