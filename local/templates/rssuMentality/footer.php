<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeTemplateLangFile(__FILE__);
?>
</div>
</main>
</div>

<footer class="footer">
    <div class="footer__container">
        <div class="footer__row">
            <!-- Колонка 1: Информация о системе -->
            <div class="footer__col footer__col--about">
                <h3 class="footer__logo-text">Психологический анализ</h3>
                <p class="footer__desc">
                    Система психологического мониторинга<br>Российского государственного социального университета
                </p>
                <div class="footer__contacts">
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon"></span>
                        <span>129226, Москва, ул. Вильгельма Пика, д.4</span>
                    </div>
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon"></span>
                        <a href="tel:+74951234567">+7 (495) 123-45-67</a>
                    </div>
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon">️</span>
                        <a href="mailto:mentality@rgsu.net">mentality@rgsu.net</a>
                    </div>
                </div>
            </div>

            <!-- Колонка 2: Статичное меню -->
            <div class="footer__col footer__col--nav">
                <h4 class="footer__col-title">Навигация</h4>
                <ul class="footer__menu">
                    <li class="footer__menu-item">
                        <a href="/" class="footer__menu-link">О проекте</a>
                    </li>
                    <li class="footer__menu-item">
                        <a href="/" class="footer__menu-link">Студентам</a>
                    </li>
                    <li class="footer__menu-item">
                        <a href="/" class="footer__menu-link">Психологам</a>
                    </li>
                    <li class="footer__menu-item">
                        <a href="/" class="footer__menu-link">Отчёты</a>
                    </li>
                    <li class="footer__menu-item">
                        <a href="/" class="footer__menu-link">Контакты</a>
                    </li>
                </ul>
            </div>

            <!-- Колонка 3: Разработчик и копирайт -->
            <div class="footer__col footer__col--dev">
                <h4 class="footer__col-title">Разработка</h4>
                <p class="footer__dev-info">
                    Система разработана<br>ООО "КьюСофт"<br>
                    © <?=date("Y")?> Все права защищены
                </p>
                <p>Ответственный со стороны разработки:</p>
                <b>Мяктинов Н.А.</b>
                <a href="mailto:n.myaktinov@qsoft.ru" class="footer__dev-link">n.myaktinov@qsoft.ru</a>
                <a href="/privacy/" class="footer__dev-link">Политика конфиденциальности</a>


            </div>
        </div>
    </div>
</footer>
</div>
</body>
</html>