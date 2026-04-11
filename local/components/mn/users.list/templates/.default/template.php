<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \CBitrixComponentTemplate $this */

\Bitrix\Main\UI\Extension::load("ui.alerts");

$signedParameters = $this->getComponent()->getSignedParameters();
$componentPath = $this->getComponent()->getPath();
?>

<div class="users-list-container"
     data-signed-parameters="<?= htmlspecialcharsbx($signedParameters) ?>"
     data-component-path="<?= htmlspecialcharsbx($componentPath) ?>">
    <?php if (empty($arResult['USERS'])): ?>
        <div class="ui-alert ui-alert-warning">Пользователи не найдены</div>
    <?php else: ?>
        <table class="users-table">
            <thead>
            <tr>
                <th>ФИО</th>
                <th>Роль</th>
                <th>Специальность</th>
                <th>Учебная группа</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($arResult['USERS'] as $user): ?>
                <tr>
                    <td><?= htmlspecialcharsbx($user['FULL_NAME']) ?></td>
                    <td><?= htmlspecialcharsbx($user['ROLE']) ?></td>
                    <td><?= htmlspecialcharsbx($user['SPECIALTY']) ?></td>
                    <td><?= htmlspecialcharsbx($user['GROUP']) ?></td>
                    <td>
                        <button class="user-detail-btn" data-user-id="<?= $user['ID'] ?>">
                            Подробнее
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $nav = $arResult['NAV_OBJECT'];
        if ($nav->getPageCount() > 1):
            ?>
            <div class="pagination">
                <?= $nav->getPages() ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Модальное окно -->
<div class="user-modal" id="userModal" style="display: none;">
    <div class="user-modal-overlay"></div>
    <div class="user-modal-content">
        <button class="user-modal-close">&times;</button>
        <div class="user-modal-body">
            <div class="user-modal-photo">
                <img id="modalUserPhoto" src="" alt="Фото пользователя">
            </div>
            <h3 id="modalUserName"></h3>
            <div class="user-tests-list">
                <h4>Пройденные тесты</h4>
                <div id="modalTestsContainer" class="tests-container"></div>
            </div>
        </div>
    </div>
</div>