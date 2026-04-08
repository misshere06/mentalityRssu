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


        quizBlock.style.display = 'none';
        completeBlock.style.display = 'none';

        let currentIndex = 0;
        let questions = [];
        let userAnswers = {};


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

        function calculateTotalScore() {
            let total = 0;
            for (let i = 0; i < questions.length; i++) {
                const q = questions[i];
                const answer = userAnswers[q.ID];
                if (!answer) continue; // вопрос без ответа – 0 баллов

                const type = normalizeQuestionType(q.PROPERTY_QUESTION_TYPE_VALUE);
                const options = q.OPTIONS;

                if (type === 'radio' || type === 'select') {
                    // один выбранный вариант
                    const selectedId = answer;
                    const option = options.find(opt => opt.ID == selectedId);
                    if (option) total += parseFloat(option.PROPERTY_SCORE_VALUE) || 0;
                }
                else if (type === 'checkbox') {
                    // несколько выбранных вариантов (JSON-массив)
                    const selectedIds = JSON.parse(answer);
                    selectedIds.forEach(id => {
                        const option = options.find(opt => opt.ID == id);
                        if (option) total += parseFloat(option.PROPERTY_SCORE_VALUE) || 0;
                    });
                }

            }
            return total;
        }
        function loadProgress() {
            return fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=getProgress'
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data) {
                        currentIndex = data.data.currentQuestion || 0;
                        userAnswers = data.data.answers || {};

                        if (data.data.status === 'completed') {
                            introBlock.style.display = 'none';
                            quizBlock.style.display = 'none';
                            completeBlock.style.display = 'block';
                            return true;
                        }
                        return true; // незавершённый прогресс
                    }
                    return false;
                })
                .catch(e => {
                    console.error('Ошибка загрузки прогресса', e);
                    return false;
                });
        }
        function saveCurrentAnswerToServer(nextIndex) {
            const currentQuestion = questions[currentIndex];
            if (!currentQuestion) return Promise.resolve();

            const formData = new FormData();
            formData.append('action', 'saveAnswer');
            formData.append('questionId', currentQuestion.ID);
            formData.append('currentIndex', nextIndex); // сохраняем следующий индекс
            formData.append('answers', JSON.stringify(userAnswers));

            const container = questionContainer;
            let answerValue = '';
            const type = normalizeQuestionType(currentQuestion.PROPERTY_QUESTION_TYPE_VALUE);

            if (type === 'radio') {
                const selected = container.querySelector('input[type="radio"]:checked');
                answerValue = selected ? selected.value : '';
            } else if (type === 'checkbox') {
                const selected = Array.from(container.querySelectorAll('input[type="checkbox"]:checked'))
                    .map(cb => cb.value);
                answerValue = JSON.stringify(selected);
            } else if (type === 'select') {
                const select = container.querySelector('select');
                answerValue = select ? select.value : '';
            } else if (type === 'text' || type === 'textarea') {
                const input = container.querySelector('input, textarea');
                answerValue = input ? input.value : '';
            }

            userAnswers[currentQuestion.ID] = answerValue;
            formData.append('answerValue', answerValue);

            return fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) console.error('Ошибка сохранения ответа');
                });
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


            if (userAnswers[q.ID]) {
                const savedValue = userAnswers[q.ID];
                const type = normalizeQuestionType(q.PROPERTY_QUESTION_TYPE_VALUE);
                if (type === 'radio') {
                    const radio = questionContainer.querySelector(`input[value="${savedValue}"]`);
                    if (radio) radio.checked = true;
                } else if (type === 'checkbox') {
                    const values = JSON.parse(savedValue);
                    values.forEach(val => {
                        const cb = questionContainer.querySelector(`input[value="${val}"]`);
                        if (cb) cb.checked = true;
                    });
                } else if (type === 'select') {
                    const select = questionContainer.querySelector('select');
                    if (select) select.value = savedValue;
                } else if (type === 'text' || type === 'textarea') {
                    const input = questionContainer.querySelector('input, textarea');
                    if (input) input.value = savedValue;
                }
            }
        }



        function goToNextQuestion() {
            const nextIndex = currentIndex + 1; // индекс следующего вопроса
            saveCurrentAnswerToServer(nextIndex).then(() => {
                if (nextIndex < questions.length) {
                    currentIndex = nextIndex;
                    renderQuestion(currentIndex);
                    updateProgress();
                } else {
                    const totalScore = calculateTotalScore();

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=completeTest&totalScore=' + totalScore + '&totalQuestions=' + questions.length + '&answers=' + JSON.stringify(userAnswers)
                    })
                        .then(res => res.json())
                        .then(() => {
                            if (quizBlock) quizBlock.style.display = 'none';
                            if (completeBlock) completeBlock.style.display = 'block';
                        });
                }
            });
        }
        loadProgress();
        if (startBtn) {
            startBtn.addEventListener('click', async () => {
                loadQuestionsFromData();
                if (questions.length === 0) {
                    alert('В тесте нет вопросов');
                    return;
                }

                const hasProgress = await loadProgress();

                if (completeBlock.style.display === 'block') {
                    return;
                }
                if (hasProgress && Object.keys(userAnswers).length > 0) {
                    introBlock.style.display = 'none';
                    quizBlock.style.display = 'block';
                    renderQuestion(currentIndex);
                    updateProgress();
                } else {
                    introBlock.style.display = 'none';
                    quizBlock.style.display = 'block';
                    currentIndex = 0;
                    userAnswers = {};
                    renderQuestion(currentIndex);
                    updateProgress();
                }
            });
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