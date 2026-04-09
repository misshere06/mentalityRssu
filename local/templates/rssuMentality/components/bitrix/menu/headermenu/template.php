<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?if (!empty($arResult)):?>
    <nav class="header__nav">
        <?
        // Loop through first-level menu items only
        foreach ($arResult as $arItem):
            if ($arItem["DEPTH_LEVEL"] != 1) continue;

            // Get inline SVG markup from parameter (expected to be a complete <svg> element)
            // Example of parameter value: '<svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="..."/></svg>'
            $svgIcon = !empty($arItem["PARAMS"]["ICON_SVG"])
                    ? $arItem["PARAMS"]["ICON_SVG"]
                    : '<svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'; // default fallback icon

            // Active class for current item
            $activeClass = ($arItem["SELECTED"]) ? "active" : "";

            // Handle restricted access items
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
               class="header__nav-item <?=$activeClass?>"
                    <?=$title ? 'title="'.$title.'"' : ''?>
                    <?=$disabledAttr ?>>
                <? // Output inline SVG (trusted content from admin panel) ?>
                <?=$svgIcon?>
                <?=$arItem["TEXT"]?>
            </a>
        <?endforeach;?>
    </nav>
<?endif?>