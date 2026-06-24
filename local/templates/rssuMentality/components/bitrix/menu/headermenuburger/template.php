<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?if (!empty($arResult)):?>
    <nav class="burger-menu__section">
        <h3 class="burger-menu__section-title">Основное меню</h3>
        <ul class="burger-menu__list">
            <?foreach ($arResult as $arItem):
                if ($arItem["DEPTH_LEVEL"] != 1) continue;

                $svgIcon = !empty($arItem["PARAMS"]["ICON_SVG"])
                        ? $arItem["PARAMS"]["ICON_SVG"]
                        : '<svg class="burger-menu__item-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';

                $activeClass = ($arItem["SELECTED"]) ? "active" : "";
                $link = ($arItem["PERMISSION"] <= "D") ? "#" : $arItem["LINK"];
                $title = ($arItem["PERMISSION"] <= "D") ? GetMessage("MENU_ITEM_ACCESS_DENIED") : "";
                ?>
                <li class="burger-menu__item">
                    <a href="<?=$link?>" class="burger-menu__link <?=$activeClass?>" <?=$title ? 'title="'.$title.'"' : ''?>>
                        <?=$svgIcon?>
                        <span><?=$arItem["TEXT"]?></span>
                    </a>
                </li>
            <?endforeach;?>
        </ul>
    </nav>
<?endif?>