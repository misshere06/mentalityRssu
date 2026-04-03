<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Создать тест");
?><!-- Блок конструктора тестов -->

        <!-- Блок конструктора тестов -->
        <div class="testcreator">
            <div class="testcreator__header">
                <div class="testcreator__title">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Конструктор тестов</span>
                </div>
                <button class="testcreator__add-btn" id="addQuestionBtn">
                    <i class="fas fa-plus-circle"></i> Добавить вопрос
                </button>
            </div>

            <div id="questionsContainer" class="testcreator__questions">
                <!-- Динамические вопросы будут добавляться сюда -->
            </div>
        </div>


</body>
</html><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>