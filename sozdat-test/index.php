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
                <button class="testcreator__create-btn" id="createTestBtn">
                    <i class="fas fa-angle-double-right"></i> Создать тест
                </button>
            </div>

            <div id="questionsContainer" class="testcreator__questions">
                <!-- Динамические вопросы будут добавляться сюда -->
            </div>
            <!-- НОВЫЙ ФУТЕР -->
            <div class="testcreator__footer">
                <div class="testcreator__footer-left">
                    <button class="testcreator__add-btn-footer" id="addQuestionBtnFooter">
                        <i class="fas fa-plus-circle"></i> Добавить вопрос
                    </button>
                            <span class="testcreator__question-counter" id="questionCounter">
                                Вопросов: <span id="counterValue">0</span>
                            </span>
                </div>
                <button class="testcreator__scroll-top-btn" id="scrollTopBtn">
                    <i class="fas fa-arrow-up"></i> Наверх
                </button>
            </div>
        </div>


</body>
</html><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>