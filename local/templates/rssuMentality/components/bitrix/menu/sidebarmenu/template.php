<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?if (!empty($arResult)):?>
    <div class="sidebar__menu">
        <?
        // Проходим только по пунктам первого уровня (корневое меню)
        foreach ($arResult as $arItem):
            if ($arItem["DEPTH_LEVEL"] != 1) continue;

            // Определяем путь к иконке (из пользовательского свойства или стандартная)
            $iconSrc = !empty($arItem["PARAMS"]["ICON_SVG"])
                    ? $arItem["PARAMS"]["ICON_SVG"]
                    : "/assets/img/svg/arrow-basic.svg";

            // Класс активного пункта
            $activeClass = ($arItem["SELECTED"]) ? "active" : "";

            // Обработка запрещённых пунктов (доступ закрыт)
            if ($arItem["PERMISSION"] <= "D"):
                $link = "#";
                $title = GetMessage("MENU_ITEM_ACCESS_DENIED");
                $disabledAttr = 'disabled';
            else:
                $link = $arItem["LINK"];
                $title = "";
                $disabledAttr = "";
            endif;
            ?>
            <a href="<?=$link?>"
               class="sidebar__menu-item <?=$activeClass?>"
                    <?=$title ? 'title="'.$title.'"' : ''?>
                    <?=$disabledAttr ? 'disabled' : ''?>>
		<span class="sidebar__menu-icon">
			<img src="<?=$iconSrc?>" alt="" class="menu-icon-img">
		</span>
                <span class="sidebar__menu-text"><?=$arItem["TEXT"]?></span>
            </a>
        <?endforeach;?>
    </div>
<?endif?>