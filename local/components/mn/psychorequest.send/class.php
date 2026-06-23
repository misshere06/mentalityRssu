<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Context;

class PsychoRequestSendComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['PSYCHO_GROUP_ID'] = (int)($arParams['PSYCHO_GROUP_ID'] ?? 0);
        $arParams['REDIRECT_URL'] = trim((string)($arParams['REDIRECT_URL'] ?? ''));
        // ID психолога: сначала из параметра, потом из URL
        $arParams['PSYCHOLOGIST_ID'] = (int)($arParams['PSYCHOLOGIST_ID'] ?? 0);
        if ($arParams['PSYCHOLOGIST_ID'] <= 0 && isset($_REQUEST['PSYCHOLOGIST_ID'])) {
            $arParams['PSYCHOLOGIST_ID'] = (int)$_REQUEST['PSYCHOLOGIST_ID'];
        }
        if ($arParams['PSYCHOLOGIST_ID'] <= 0) {
            $curPage = $GLOBALS['APPLICATION']->GetCurPage();
            $parts = explode('/', trim($curPage, '/'));
            $last = end($parts);
            if (is_numeric($last)) {
                $arParams['PSYCHOLOGIST_ID'] = (int)$last;
            }
        }
        return $arParams;
    }

    public function executeComponent()
    {
        global $USER;
        Loader::includeModule('iblock');
        Loader::includeModule('main');

        $request = Context::getCurrent()->getRequest();

        // Загружаем список доступных психологов
        $this->arResult['PSYCHOLOGISTS'] = $this->getPsychologists();
        $this->arResult['ERROR'] = '';

        // Определяем выбранного психолога
        $selectedId = $this->arParams['PSYCHOLOGIST_ID'];
        $availableIds = array_column($this->arResult['PSYCHOLOGISTS'], 'ID');
        $this->arResult['SELECTED_PSYCHOLOGIST_ID'] = in_array($selectedId, $availableIds) ? $selectedId : 0;

        if ($request->isPost() && check_bitrix_sessid()) {
            $psychologistId = (int)$request->getPost('PSYCHOLOGIST_ID');
            $preferredDate = trim($request->getPost('PREFERRED_DATE'));
            $reason = trim($request->getPost('REASON'));

            // Валидация
            if ($psychologistId <= 0 || !in_array($psychologistId, $availableIds)) {
                $this->arResult['ERROR'] = 'Выбранный психолог недоступен.';
            } elseif (empty($preferredDate)) {
                $this->arResult['ERROR'] = 'Укажите желаемую дату.';
            } elseif (empty($reason)) {
                $this->arResult['ERROR'] = 'Опишите причину обращения.';
            }

            if (empty($this->arResult['ERROR'])) {
                $el = new CIBlockElement;
                $arFields = [
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    'NAME' => 'Заявка от ' . date('d.m.Y H:i'),
                    'ACTIVE' => 'Y',
                    'PROPERTY_VALUES' => [
                        'PSYCHOLOGIST_ID' => $psychologistId,
                        'STUDENT_ID' => $USER->GetID(),
                        'STATUS' => 'new',
                        'PREFERRED_DATE' => $preferredDate,
                        'REASON' => $reason,
                    ],
                ];

                if ($elId = $el->Add($arFields)) {
                    $redirectUrl = $this->arParams['REDIRECT_URL'];
                    if (empty($redirectUrl)) {
                        $redirectUrl = SITE_DIR . 'psycho/requests/';
                    }
                    LocalRedirect($redirectUrl);
                    exit;
                } else {
                    $this->arResult['ERROR'] = 'Ошибка сохранения заявки: ' . $el->LAST_ERROR;
                }
            }
        }

        $this->includeComponentTemplate();
    }

    protected function getPsychologists()
    {
        $groupId = $this->arParams['PSYCHO_GROUP_ID'];
        if ($groupId <= 0) {
            return [];
        }

        $groupUsers = UserGroupTable::getList([
            'filter' => ['GROUP_ID' => $groupId],
            'select' => ['USER_ID'],
        ])->fetchAll();
        $userIds = array_column($groupUsers, 'USER_ID');
        if (empty($userIds)) {
            return [];
        }

        $users = UserTable::getList([
            'filter' => [
                'ID' => $userIds,
                'ACTIVE' => 'Y',
                'UF_ACCEPT_REQUESTS' => 1,
            ],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
            'order' => ['LAST_NAME' => 'ASC'],
        ])->fetchAll();

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'ID' => $user['ID'],
                'FULL_NAME' => trim("{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}"),
            ];
        }
        return $result;
    }
}