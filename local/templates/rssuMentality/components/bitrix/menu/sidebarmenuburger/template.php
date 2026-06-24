<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?if (!empty($arResult)):?>
    <nav class="burger-menu__section">
        <h3 class="burger-menu__section-title">Навигация</h3>
        <ul class="burger-menu__list">
            <?foreach ($arResult as $arItem):
                if ($arItem["DEPTH_LEVEL"] != 1) continue;

                // Иконка из параметров или стандартная стрелка
                $iconSrc = !empty($arItem["PARAMS"]["ICON_SVG"])
                        ? $arItem["PARAMS"]["ICON_SVG"]
                        : "/assets/img/svg/arrow-basic.svg";

                $activeClass = ($arItem["SELECTED"]) ? "active" : "";
                $link = ($arItem["PERMISSION"] <= "D") ? "#" : $arItem["LINK"];
                $title = ($arItem["PERMISSION"] <= "D") ? GetMessage("MENU_ITEM_ACCESS_DENIED") : "";
                ?>
                <li class="burger-menu__item">
                    <a href="<?=$link?>" class="burger-menu__link <?=$activeClass?>" <?=$title ? 'title="'.$title.'"' : ''?>>
                        <span class="burger-menu__item-icon-img"><img src="<?=$iconSrc?>" alt=""></span>
                        <span><?=$arItem["TEXT"]?></span>
                    </a>
                </li>
            <?endforeach;?>
        </ul>
    </nav>
<?endif?>