<?
$aMenuLinks = Array(
    Array(
        "Создать тест",
        "/sozdat-test/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg"
            // Если нужно скрыть от гостей – добавьте "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(1,8))"
    ),
    Array(
        "Тесты",
        "/tests/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg",
            "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(6))"
    ),
    Array(
        "Редактировать тест",
        "/testeditor/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg",
            "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(1,8))"
    ),
    Array(
        "СДО РГСУ",
        "https://sdo.rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg"),
        "" // условие пустое – показывать всем, но скрыть от гостей через HIDE_FOR_GUEST (если нужно)
    ),
    Array(
        "Сайт РГСУ",
        "https://rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg"
            // HIDE_FOR_GUEST не задан – показывать всем
        ),
        ""
    ),
    Array(
        "Личный кабинет студента",
        "https://my.rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg"
        ),
        ""
    ),
    Array(
        "Войти на сайт",
        "/auth/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/icon-tests.svg"
        ),
        ""
    )
);
?>