<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Политика конфиденциальности");
?>
    <div class="main__container">
        <h1 class="main__title">Политика конфиденциальности</h1>

        <div class="info-card">
            <div class="info-card__content">
                <p>Редакция №2 от «31» августа 2022 г.</p>

                <h3>Термины и определения</h3>
                <ul class="info-card__list">
                    <li><strong>Сайт</strong> – сайт Оператора, размещенный по адресу: <a href="https://mentality.rgsu.net">https://mentality.rgsu.net</a> (включая поддомены)</li>
                    <li><strong>Субъект</strong> – дееспособное физическое лицо, использующее Сайт, чьи Персональные данные обрабатывает Оператор</li>
                    <li><strong>Персональные данные</strong> – любая информация, относящаяся прямо или косвенно к определенному или определяемому Пользователю</li>
                    <li><strong>Обработка Персональных данных</strong> – любое действие (операция) или совокупность действий (операций) с Персональными данными, совершаемых с использованием средств автоматизации или без их использования...</li>
                </ul>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card__content">
                <h3>1. Общие положения</h3>
                <ol class="info-card__list">
                    <li>Политика является локальным документом Оператора...</li>
                    <!-- Остальные пункты аналогично, оформленные через ol/ul -->
                </ol>
            </div>
        </div>

        <!-- Повторить секции 2,3,... используя info-card -->
        <!-- Ниже пример таблицы в карточке -->
        <div class="info-card">
            <h3 class="info-card__title">2. Цели обработки</h3>
            <div class="info-card__content">
                <table class="table"> <!-- предполагаем, что у нас есть класс .table в components/_tables.scss -->
                    <thead>
                    <tr>
                        <th>Цель</th>
                        <th>Персональные данные</th>
                        <th>Категория</th>
                        <th>Срок обработки</th>
                        <th>Порядок уничтожения</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Обеспечение работы Сайта</td>
                        <td>ФИО, номер телефона, адрес эл. почты, cookies</td>
                        <td>Общие</td>
                        <td>До достижения цели или отзыва согласия</td>
                        <td>Удаление из базы данных</td>
                    </tr>
                    <!-- ... -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>