<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeTemplateLangFile(__FILE__);

use Bitrix\Main\Page\Asset;


Asset::getInstance()->addCss('/dist/styles.bundle.css');
Asset::getInstance()->addJs('/dist/main.bundle.js');
?>

<!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>">
<head>
    <? $APPLICATION->ShowHead(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><? $APPLICATION->ShowTitle(); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<? $APPLICATION->ShowPanel(); ?>
<div class="app">
    <!-- Burger menu overlay - hidden by default -->
    <div class="burger-menu">
        <div class="burger-menu__header">
            <span class="burger-menu__title">Mentality RGSU</span>
            <button class="burger-menu__close">×</button>
        </div>
        <div class="burger-menu__content">
            <div class="burger-menu__search">
                <input type="search" class="burger-menu__search-input" placeholder="Search...">
                <button class="burger-menu__search-button">Search</button>
            </div>
            <nav class="burger-menu__nav">
                <a href="#" class="burger-menu__nav-item active">Dashboard</a>
                <a href="#" class="burger-menu__nav-item">Students</a>
                <a href="#" class="burger-menu__nav-item">Psychologists</a>
                <a href="#" class="burger-menu__nav-item">Reports</a>
            </nav>
            

            <div class="burger-menu__sidebar-items">
                <h3 class="burger-menu__section-title">Sidebar Menu</h3>
                <a href="#" class="burger-menu__sidebar-item active">
                    <span class="burger-menu__sidebar-icon">🏠</span>
                    <span class="burger-menu__sidebar-text">Dashboard</span>
                </a>
                <a href="#" class="burger-menu__sidebar-item">
                    <span class="burger-menu__sidebar-icon">👥</span>
                    <span class="burger-menu__sidebar-text">Students</span>
                </a>
                <a href="#" class="burger-menu__sidebar-item">
                    <span class="burger-menu__sidebar-icon">📊</span>
                    <span class="burger-menu__sidebar-text">Psychologists</span>
                </a>
                <a href="#" class="burger-menu__sidebar-item">
                    <span class="burger-menu__sidebar-icon">📈</span>
                    <span class="burger-menu__sidebar-text">Reports</span>
                </a>
                <a href="#" class="burger-menu__sidebar-item">
                    <span class="burger-menu__sidebar-icon">⚙️</span>
                    <span class="burger-menu__sidebar-text">Settings</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Header with proper semantic tags - full width -->
    <header class="header">
        <div class="header__container">


            <div class="logo header__logo">
                <div class="logo__image">
                    <svg class="icon icon--logo logo__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 49" width="44" height="49">
                        <path fill-rule="evenodd" d="M26.954 17.211c1.153 0 2.25-.239 3.244-.67 3.95 1.404 6.776 5.165 6.776 9.584v2.413a5.698 5.698 0 0 0-.65-.037c-3.103 0-5.618 2.508-5.618 5.6a5.56 5.56 0 0 0 .814 2.905 7.908 7.908 0 0 0-1.396 1.29 8.37 8.37 0 0 0-2.33-3.834 6.724 6.724 0 0 0-1.538-9.34 10.239 10.239 0 0 0-3.904-8.028 8.05 8.05 0 0 0 .168-.222 10.41 10.41 0 0 1 1.029-.404 8.123 8.123 0 0 0 3.405.743Zm12.313 8.914v3.206A5.593 5.593 0 0 1 41 37.203a7.822 7.822 0 0 1 3 6.162V48h-2.231v-4.635c0-1.889-.934-3.56-2.367-4.579a5.604 5.604 0 0 1-3.079.915c-1.22 0-2.35-.388-3.27-1.046a5.612 5.612 0 0 0-2.56 4.71V48h-2.29v-7.44a6.128 6.128 0 0 0-1.982-4.518 6.744 6.744 0 0 1-3.947 1.267 6.745 6.745 0 0 1-4.21-1.466 6.133 6.133 0 0 0-2.207 4.717V48h-2.231v-2.553a5.848 5.848 0 0 0-2.45-3.79 5.495 5.495 0 0 1-3.425 1.19A5.495 5.495 0 0 1 4.42 41.73a5.835 5.835 0 0 0-2.437 4.745V48H0v-1.525a7.804 7.804 0 0 1 3.077-6.213 5.452 5.452 0 0 1-.834-2.905 5.492 5.492 0 0 1 3.41-5.078v-7.136a10.24 10.24 0 0 1 3.905-8.05 8.055 8.055 0 0 1-1.734-5.004c0-4.476 3.64-8.105 8.13-8.105 1.445 0 2.802.375 3.977 1.034A8.132 8.132 0 0 1 26.954 1c4.49 0 8.131 3.629 8.131 8.106 0 2.345-1 4.458-2.597 5.938 4.026 2.07 6.779 6.255 6.779 11.081ZM13.258 37.357c0-2.967-2.36-5.384-5.311-5.487v-6.727c0-2.65 1.295-5 3.29-6.451a8.111 8.111 0 0 0 4.718 1.503 8.112 8.112 0 0 0 4.717-1.503 7.975 7.975 0 0 1 3.212 5.33 6.795 6.795 0 0 0-1.61-.192c-3.734 0-6.76 3.017-6.76 6.74 0 1.332.388 2.574 1.057 3.62a8.349 8.349 0 0 0-2.945 6.37v.616a7.886 7.886 0 0 0-1.14-1.016c.49-.82.772-1.779.772-2.803ZM26.954 3.158a5.878 5.878 0 0 0-5.242 3.208 8.068 8.068 0 0 1 2.374 5.723 8.11 8.11 0 0 1-.227 1.908c.9.556 1.96.877 3.095.877 3.245 0 5.876-2.623 5.876-5.858 0-3.235-2.63-5.858-5.876-5.858Zm-5.123 8.931c0 3.236-2.631 5.858-5.876 5.858-3.246 0-5.877-2.622-5.877-5.858 0-3.235 2.631-5.857 5.877-5.857 3.245 0 5.876 2.622 5.876 5.857Zm.443 23.068a4.595 4.595 0 0 0 4.602-4.588 4.595 4.595 0 0 0-4.602-4.587 4.595 4.595 0 0 0-4.602 4.587 4.595 4.595 0 0 0 4.602 4.588ZM39.941 34.1a3.612 3.612 0 0 1-3.618 3.607 3.612 3.612 0 0 1-3.617-3.607 3.612 3.612 0 0 1 3.617-3.606 3.612 3.612 0 0 1 3.618 3.606Zm-28.844 3.256a3.342 3.342 0 0 1-3.346 3.336 3.342 3.342 0 0 1-3.347-3.336A3.342 3.342 0 0 1 7.75 34.02a3.342 3.342 0 0 1 3.346 3.337Z" clip-rule="evenodd"></path>
                    </svg>
                    <svg class="icon icon--rgsu logo__rgsu" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 14" width="44" height="14">
                        <path d="M4.998.27c1.29 0 2.375.435 3.256 1.307.881.872 1.321 1.942 1.321 3.211 0 1.27-.44 2.34-1.32 3.212-.882.872-1.967 1.308-3.257 1.308H2.643v4.423H0V.269h4.998Zm0 6.557c.562 0 1.028-.192 1.398-.577.37-.397.556-.885.556-1.462 0-.59-.185-1.076-.556-1.461-.37-.385-.836-.577-1.398-.577H2.643v4.077h2.355ZM19.205.27v2.538h-4.979V13.73h-2.643V.269h7.622ZM26.495 14c-2.017 0-3.683-.673-4.998-2.02-1.315-1.345-1.973-3.006-1.973-4.98 0-1.987.658-3.647 1.973-4.98C22.812.672 24.477 0 26.495 0c1.213 0 2.33.288 3.351.865 1.035.564 1.839 1.334 2.413 2.308L29.98 4.5a3.484 3.484 0 0 0-1.417-1.404c-.612-.346-1.302-.52-2.068-.52-1.302 0-2.356.411-3.16 1.232-.791.82-1.187 1.884-1.187 3.192 0 1.295.396 2.353 1.187 3.173.804.82 1.858 1.23 3.16 1.23.766 0 1.456-.166 2.068-.5A3.484 3.484 0 0 0 29.981 9.5l2.278 1.327a6.339 6.339 0 0 1-2.393 2.327c-1.022.564-2.145.846-3.371.846ZM41.357.27H44l-4.022 9.75c-1.072 2.615-2.834 3.852-5.285 3.71v-2.48c.715.064 1.29-.032 1.723-.289.447-.269.817-.698 1.111-1.288L33.141.27h2.643l2.93 6.5 2.643-6.5Z"></path>
                    </svg>
                    <img class="logo__z" src="https://my.rgsu.net/assets/front/img/z.svg" alt="">
                </div>
                <div class="logo__caption">
                    <div class="logo__tag"><span translate="no">#МойРГСУ</span></div>
                    <div class="logo__title">Психологический анализ</div>
                </div>
            </div>

            <nav class="header__nav">
                <a href="#" class="header__nav-item active">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 13h6c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v8c0 .55.45 1 1 1zm0 8h6c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1zm10 0h6c.55 0 1-.45 1-1v-8c0-.55-.45-1-1-1h-6c-.55 0-1 .45-1 1v8c0 .55.45 1 1 1zM13 4v4c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1h-6c-.55 0-1 .45-1 1z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="#" class="header__nav-item">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    Students
                </a>
                <a href="#" class="header__nav-item">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 2h6v2h-6V6zm0 4h6v2h-6v-2zm-6 0h4v2H6v-2zm0 4h4v2H6v-2z"/>
                    </svg>
                    Psychologists
                </a>
                <a href="#" class="header__nav-item">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 14H7v-2h4v2zm0-4H7v-2h4v2zm0-4H7V7h4v2zm6 6h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V7h4v2z"/>
                    </svg>
                    Reports
                </a>
            </nav>
            <div class="header__search">
                <button class="header__search-close">×</button>
                <input type="search" class="header__search-input" placeholder="Search...">
                <button class="header__search-button" aria-label="Search">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.5 15.5L19 19M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <button class="header__burger-menu-toggle">☰</button>
        </div>
    </header>

    <div class="app__content">
        <!-- Left sidebar with user info and menu -->
        <aside class="sidebar">
            <?$APPLICATION->IncludeComponent(
	"bitrix:main.user.link", 
	"sidebar", 
	array(
		"SHOW_ACTIONS" => "Y",
		"NAME_TEMPLATE" => "#LAST_NAME# #NAME_SHORT#",
		"SHOW_LOGIN" => "Y",
		"USE_THUMBNAIL_LIST" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "7200",
		"THUMBNAIL_LIST_SIZE" => "30",
		"COMPONENT_TEMPLATE" => "sidebar",
		"ID" => "2"
	),
	false
);?>

            <?$APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "sidebarmenu",
                    array(
                            "COMPONENT_TEMPLATE" => "sidebarmenu",
                            "ROOT_MENU_TYPE" => "top",
                            "MENU_CACHE_TYPE" => "N",
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(
                            ),
                            "MAX_LEVEL" => "1",
                            "CHILD_MENU_TYPE" => "left",
                            "USE_EXT" => "N",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "N",
                            "MENU_THEME" => "site"
                    ),
                    false
            );?>

        </aside>

        <!-- Main content block -->
        <main class="main">
            <div class="main__container">