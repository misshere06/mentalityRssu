<?
$aMenuLinks = Array(
    Array(
        "Создать тест",
        "/sozdat-test/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic8.svg"
            // Если нужно скрыть от гостей – добавьте "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(1,8))"
    ),
    Array(
        "Тесты",
        "/tests/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic2.svg",
            "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(6))"
    ),
    Array(
        "Редактировать тест",
        "/testeditor/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic3.svg",
            "HIDE_FOR_GUEST" => "Y"
        ),
        "CSite::InGroup(array(1,8))"
    ),
    Array(
        "СДО РГСУ",
        "https://sdo.rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic4.svg"),
        "" // условие пустое – показывать всем, но скрыть от гостей через HIDE_FOR_GUEST (если нужно)
    ),
    Array(
        "Сайт РГСУ",
        "https://rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic5.svg"
            // HIDE_FOR_GUEST не задан – показывать всем
        ),
        ""
    ),
    Array(
        "Личный кабинет студента",
        "https://my.rgsu.net/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic6.svg"
        ),
        ""
    ),
    Array(
        "Войти на сайт",
        "/auth/",
        Array(),
        Array(
            "ICON_SVG" => "/assets/img/svg/arrow-basic7.svg"
        ),
        ""
    )
);
?>