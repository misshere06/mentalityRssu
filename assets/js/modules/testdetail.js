(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.onequestion-test');
        const introBlock = document.querySelector('.js-test-intro');
        const quizBlock = document.querySelector('.js-test-quiz');
        const completeBlock = document.querySelector('.js-test-complete');
        const startBtn = document.querySelector('.js-start-btn');
        const nextBtn = document.querySelector('.js-next-btn');
        const progressText = document.querySelector('.js-progress-text');
        const progressFill = document.querySelector('.js-progress-fill');
        const questionContainer = document.querySelector('.js-question-container');

        let currentIndex = 0;
        let questions = [];

        // Загрузка вопросов из data-атрибута
        function loadQuestionsFromData() {
            if (container && container.dataset.questions) {
                try {
                    questions = JSON.parse(container.dataset.questions);
                    console.log('Загружено вопросов из data-атрибута:', questions.length);
                } catch(e) {
                    console.error('Ошибка парсинга data-questions', e);
                    questions = [];
                }
            } else {
                console.error('Контейнер .onequestion-test или data-questions не найден');
                questions = [];
            }
            return questions;
        }

        // Нормализация типа вопроса
        function normalizeQuestionType(typeValue) {
            if (typeof typeValue !== 'string') return 'text';
            const lower = typeValue.toLowerCase();
            if (lower.includes('radio') || lower === 'один выбор') return 'radio';
            if (lower.includes('checkbox') || lower === 'несколько выборов') return 'checkbox';
            if (lower.includes('select') || lower === 'выпадающий список') return 'select';
            if (lower.includes('textarea') || lower === 'длинный текст') return 'textarea';
            if (lower.includes('text') || lower === 'короткий текст') return 'text';
            return 'text';
        }

        function updateProgress() {
            if (!questions.length) return;
            const percent = ((currentIndex + 1) / questions.length) * 100;
            if (progressText) progressText.textContent = `Вопрос ${currentIndex + 1} из ${questions.length}`;
            if (progressFill) progressFill.style.width = `${percent}%`;
        }

        function renderQuestion(index) {
            const q = questions[index];
            if (!q) return;

            const rawType = q.PROPERTY_QUESTION_TYPE_VALUE || '';
            const type = normalizeQuestionType(rawType);
            let imageHtml = '';
            // Используем PICTURE_PATH, подготовленный в PHP
            const imgPath = q.PICTURE_PATH || '';
            if (imgPath) {
                imageHtml = `<div class="onequestion-test__question-image"><img src="${imgPath}" alt=""></div>`;
            }

            let optionsHtml = '';
            switch (type) {
                case 'radio':
                case 'checkbox':
                    if (q.OPTIONS && q.OPTIONS.length) {
                        const inputType = type === 'radio' ? 'radio' : 'checkbox';
                        const nameAttr = type === 'radio' ? `q_${q.ID}` : `q_${q.ID}[]`;
                        q.OPTIONS.forEach(opt => {
                            optionsHtml += `
                                <label class="onequestion-test__option">
                                    <input type="${inputType}" name="${nameAttr}" value="${opt.ID}">
                                    <span class="onequestion-test__option-text">${escapeHtml(opt.NAME)}</span>
                                </label>
                            `;
                        });
                    } else {
                        optionsHtml = '<p class="onequestion-test__error">Нет вариантов ответа</p>';
                    }
                    break;
                case 'select':
                    optionsHtml = `<select name="q_${q.ID}" class="onequestion-test__select">`;
                    optionsHtml += `<option value="">-- Выберите вариант --</option>`;
                    if (q.OPTIONS && q.OPTIONS.length) {
                        q.OPTIONS.forEach(opt => {
                            optionsHtml += `<option value="${opt.ID}">${escapeHtml(opt.NAME)}</option>`;
                        });
                    } else {
                        optionsHtml += `<option disabled>Нет вариантов</option>`;
                    }
                    optionsHtml += `</select>`;
                    break;
                case 'text':
                    optionsHtml = `<input type="text" name="q_${q.ID}" class="onequestion-test__text-input" placeholder="Ваш ответ">`;
                    break;
                case 'textarea':
                    optionsHtml = `<textarea name="q_${q.ID}" class="onequestion-test__textarea" rows="3" placeholder="Ваш ответ"></textarea>`;
                    break;
                default:
                    optionsHtml = `<p class="onequestion-test__error">Неизвестный тип вопроса</p>`;
            }

            const html = `
                <div class="onequestion-test__question" data-id="${q.ID}">
                    <div class="onequestion-test__question-text">${escapeHtml(q.NAME)}</div>
                    ${imageHtml}
                    <div class="onequestion-test__options" data-type="${type}">
                        ${optionsHtml}
                    </div>
                </div>
            `;
            if (questionContainer) questionContainer.innerHTML = html;
        }

        function saveCurrentAnswer() {
            alert('Ответ сохранён (демо-режим)');
        }

        function goToNextQuestion() {
            saveCurrentAnswer();
            if (currentIndex + 1 < questions.length) {
                currentIndex++;
                renderQuestion(currentIndex);
                updateProgress();
            } else {
                if (quizBlock) quizBlock.style.display = 'none';
                if (completeBlock) completeBlock.style.display = 'block';
            }
        }

        if (startBtn) {
            startBtn.addEventListener('click', () => {
                // Загружаем вопросы из data-атрибута при клике
                loadQuestionsFromData();
                console.log('questions length on click:', questions.length);
                if (questions.length === 0) {
                    alert('В тесте нет вопросов');
                    return;
                }
                if (introBlock) introBlock.style.display = 'none';
                if (quizBlock) quizBlock.style.display = 'block';
                currentIndex = 0;
                renderQuestion(currentIndex);
                updateProgress();
            });
        } else {
            console.error('Кнопка .js-start-btn не найдена');
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', goToNextQuestion);
        } else {
            console.error('Кнопка .js-next-btn не найдена');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
    });
})();