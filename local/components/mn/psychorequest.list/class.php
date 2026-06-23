<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;

class PsychoRequestListComponent extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [
            'getRequestDetail' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(['POST']),
                    new ActionFilter\Csrf(),
                ],
                'arguments' => ['requestId', 'mode', 'iblockId'],
            ],
            'updateStatus' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(['POST']),
                    new ActionFilter\Csrf(),
                ],
                'arguments' => ['requestId', 'newStatus', 'mode', 'iblockId'],
            ],
            'cancelRequest' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(['POST']),
                    new ActionFilter\Csrf(),
                ],
                'arguments' => ['requestId', 'mode', 'iblockId'],
            ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['PAGE_SIZE'] = (int)($arParams['PAGE_SIZE'] ?? 10);
        return $arParams;
    }

    public function executeComponent()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('main');

        $this->arResult['MODE'] = ($this->getTemplateName() === 'psycho') ? 'psycho' : 'student';

        if ($this->arParams['IBLOCK_ID'] <= 0) {
            $this->arResult['ITEMS'] = [];
            $this->includeComponentTemplate();
            return;
        }

        $this->arResult['ITEMS'] = $this->getRequests();
        $this->arResult['NAV_STRING'] = $this->getNavString();

        $this->includeComponentTemplate();
    }

    protected function getRequests()
    {
        global $USER;
        $userId = $USER->GetID();
        $iblockId = $this->arParams['IBLOCK_ID'];

        $nav = new \CIBlockResult();
        $nav->NavStart($this->arParams['PAGE_SIZE']);

        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
        if ($this->arResult['MODE'] === 'student') {
            $filter['PROPERTY_STUDENT_ID'] = $userId;
        } else {
            $filter['PROPERTY_PSYCHOLOGIST_ID'] = $userId;
        }

        $res = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            $filter,
            false,
            $nav,
            ['ID', 'NAME', 'DATE_CREATE',
                'PROPERTY_PSYCHOLOGIST_ID', 'PROPERTY_STUDENT_ID',
                'PROPERTY_STATUS', 'PROPERTY_PREFERRED_DATE', 'PROPERTY_REASON']
        );

        $items = [];
        $userIds = [];
        while ($item = $res->GetNext()) {
            $items[] = $item;
            $userIds[] = $item['PROPERTY_PSYCHOLOGIST_ID_VALUE'];
            $userIds[] = $item['PROPERTY_STUDENT_ID_VALUE'];
        }

        $userNames = [];
        if (!empty($userIds)) {
            $userIds = array_unique($userIds);
            $users = UserTable::getList([
                'filter' => ['ID' => $userIds],
                'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
            ]);
            while ($u = $users->fetch()) {
                $userNames[$u['ID']] = trim("{$u['LAST_NAME']} {$u['NAME']} {$u['SECOND_NAME']}");
            }
        }

        $result = [];
        foreach ($items as $item) {
            $psychoId = $item['PROPERTY_PSYCHOLOGIST_ID_VALUE'];
            $studentId = $item['PROPERTY_STUDENT_ID_VALUE'];
            $result[] = [
                'ID' => $item['ID'],
                'PSYCHOLOGIST_ID' => $psychoId,
                'STUDENT_ID' => $studentId,
                'PSYCHOLOGIST_NAME' => $userNames[$psychoId] ?? '—',
                'STUDENT_NAME' => $userNames[$studentId] ?? '—',
                'STATUS' => $item['PROPERTY_STATUS_VALUE'] ?? '',
                'PREFERRED_DATE' => $item['PROPERTY_PREFERRED_DATE_VALUE'] ?? '',
                'REASON' => $item['PROPERTY_REASON_VALUE'] ?? '',
                'DATE_CREATE' => $item['DATE_CREATE'],
            ];
        }

        $this->arResult['NAV_RESULT'] = $nav;
        return $result;
    }

    protected function getNavString()
    {
        if ($this->arResult['NAV_RESULT']) {
            return $this->arResult['NAV_RESULT']->GetPageNavString('Заявки:', 'modern');
        }
        return '';
    }

    // ----- AJAX экшены -----

    public function getRequestDetailAction($requestId, $mode, $iblockId)
    {
        Loader::includeModule('iblock');
        $requestId = (int)$requestId;
        $iblockId = (int)$iblockId;
        if ($requestId <= 0 || $iblockId <= 0) {
            return ['error' => 'Неверные параметры'];
        }

        $item = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $requestId],
            false, false,
            ['ID', 'NAME', 'DATE_CREATE',
                'PROPERTY_PSYCHOLOGIST_ID', 'PROPERTY_STUDENT_ID',
                'PROPERTY_STATUS', 'PROPERTY_PREFERRED_DATE', 'PROPERTY_REASON']
        )->GetNext();

        if (!$item) {
            return ['error' => 'Заявка не найдена'];
        }

        global $USER;
        $userId = $USER->GetID();
        if ($mode === 'student' && $item['PROPERTY_STUDENT_ID_VALUE'] != $userId) {
            return ['error' => 'Нет доступа к этой заявке'];
        }
        if ($mode === 'psycho' && $item['PROPERTY_PSYCHOLOGIST_ID_VALUE'] != $userId) {
            return ['error' => 'Нет доступа к этой заявке'];
        }

        $psychoId = $item['PROPERTY_PSYCHOLOGIST_ID_VALUE'];
        $studentId = $item['PROPERTY_STUDENT_ID_VALUE'];

        $users = UserTable::getList([
            'filter' => ['ID' => [$psychoId, $studentId]],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME',
                'UF_ABOUT', 'UF_EXPERIENCE', 'UF_SPECIALIZATION'],
        ])->fetchAll();
        $userData = [];
        foreach ($users as $u) {
            $userData[$u['ID']] = [
                'FULL_NAME' => trim("{$u['LAST_NAME']} {$u['NAME']} {$u['SECOND_NAME']}"),
                'ABOUT' => $u['UF_ABOUT'] ?? '',
                'EXPERIENCE' => $u['UF_EXPERIENCE'] ?? '',
                'SPECIALIZATION' => $u['UF_SPECIALIZATION'] ?? '',
            ];
        }

        return [
            'ID' => $item['ID'],
            'STATUS' => $item['PROPERTY_STATUS_VALUE'],
            'PREFERRED_DATE' => $item['PROPERTY_PREFERRED_DATE_VALUE'],
            'REASON' => $item['PROPERTY_REASON_VALUE'],
            'DATE_CREATE' => $item['DATE_CREATE'],
            'PSYCHOLOGIST' => $userData[$psychoId] ?? [],
            'STUDENT' => $userData[$studentId] ?? [],
            'CAN_EDIT' => ($mode === 'psycho'),
        ];
    }

    public function cancelRequestAction($requestId, $mode, $iblockId)
    {
        if ($mode !== 'student') {
            return ['error' => 'Только студент может отменить заявку'];
        }

        Loader::includeModule('iblock');
        $requestId = (int)$requestId;
        $iblockId = (int)$iblockId;
        if ($requestId <= 0 || $iblockId <= 0) {
            return ['error' => 'Неверные параметры'];
        }

        global $USER;
        $item = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $requestId],
            false, false,
            ['ID', 'PROPERTY_STUDENT_ID', 'PROPERTY_STATUS']
        )->GetNext();

        if (!$item || $item['PROPERTY_STUDENT_ID_VALUE'] != $USER->GetID()) {
            return ['error' => 'Заявка не найдена или нет доступа'];
        }

        // Получаем ID значения "Отменена" по XML_ID = 'cancelled'
        $statusEnumId = $this->getEnumIdByXmlId($iblockId, 'STATUS', 'cancelled');
        if (!$statusEnumId) {
            return ['error' => 'Не удалось найти значение статуса "Отменена"'];
        }

        // Обновляем только статус, используя ID перечисления
        \CIBlockElement::SetPropertyValuesEx($requestId, $iblockId, ['STATUS' => $statusEnumId]);

        return ['success' => true];
    }

    public function updateStatusAction($requestId, $newStatus, $mode, $iblockId)
    {
        if ($mode !== 'psycho') {
            return ['error' => 'Только психолог может менять статус'];
        }

        Loader::includeModule('iblock');
        $requestId = (int)$requestId;
        $iblockId = (int)$iblockId;
        $newStatus = trim($newStatus);
        if ($requestId <= 0 || $iblockId <= 0 || empty($newStatus)) {
            return ['error' => 'Неверные параметры'];
        }

        global $USER;
        $item = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $requestId],
            false, false,
            ['ID', 'PROPERTY_PSYCHOLOGIST_ID']
        )->GetNext();

        if (!$item || $item['PROPERTY_PSYCHOLOGIST_ID_VALUE'] != $USER->GetID()) {
            return ['error' => 'Заявка не найдена или нет доступа'];
        }

        // Получаем ID значения по XML_ID (new, accepted, completed, cancelled)
        $statusEnumId = $this->getEnumIdByXmlId($iblockId, 'STATUS', $newStatus);
        if (!$statusEnumId) {
            return ['error' => 'Неверный статус: ' . $newStatus];
        }

        \CIBlockElement::SetPropertyValuesEx($requestId, $iblockId, ['STATUS' => $statusEnumId]);

        return ['success' => true];
    }

    /**
     * Возвращает ID значения перечисления для свойства типа "список" по его XML_ID
     */
    protected function getEnumIdByXmlId(int $iblockId, string $propCode, string $xmlId): ?int
    {
        $enum = \CIBlockPropertyEnum::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'CODE' => $propCode, 'XML_ID' => $xmlId]
        )->Fetch();
        return $enum ? (int)$enum['ID'] : null;
    }
}