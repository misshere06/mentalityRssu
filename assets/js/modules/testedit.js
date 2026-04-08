// assets/js/modules/testedit.js
export function initTesteditor() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initTesteditor());
        return;
    }

    const container = document.getElementById('questionsContainer');
    const addBtn = document.getElementById('addQuestionBtn');

    if (!container || !addBtn) {
        console.warn('testedit: required elements not found');
        return;
    }

    const TYPES_WITH_OPTIONS = ['radio', 'checkbox', 'select'];

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    function createOptionElement(text = '', score = '') {
        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <input type="text" class="option-text" value="${escapeHtml(text)}" placeholder="Вариант ответа">
            <input type="number" class="option-score" value="${escapeHtml(score)}" placeholder="Баллы">
            <button type="button" class="remove-option"><i class="fas fa-times"></i></button>
        `;
        div.querySelector('.remove-option').addEventListener('click', () => div.remove());
        return div;
    }

    function buildOptionsArea(type, currentOptions = []) {
        const optionsArea = document.createElement('div');
        optionsArea.className = 'test-editor__options-area';

        if (TYPES_WITH_OPTIONS.includes(type)) {
            const title = document.createElement('div');
            title.className = 'options-title';
            title.innerHTML = '<i class="fas fa-list-ul"></i> Варианты ответов (с баллами)';
            const list = document.createElement('div');
            list.className = 'options-list';
            if (currentOptions.length === 0) {
                list.appendChild(createOptionElement('', ''));
            } else {
                currentOptions.forEach(opt => {
                    list.appendChild(createOptionElement(opt.text, opt.score));
                });
            }
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'add-option-btn';
            addBtn.innerHTML = '+ Добавить вариант';
            addBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                list.appendChild(createOptionElement('', ''));
            });
            optionsArea.appendChild(title);
            optionsArea.appendChild(list);
            optionsArea.appendChild(addBtn);
        } else {
            const info = document.createElement('div');
            info.className = 'test-editor__info-message';
            if (type === 'text') info.innerHTML = '<i class="fas fa-font"></i> Тип "Короткий текст" — респондент введёт текст. Баллы не присваиваются.';
            else if (type === 'textarea') info.innerHTML = '<i class="fas fa-paragraph"></i> Тип "Длинный текст" — многострочное поле. Баллы не нужны.';
            else info.innerHTML = '<i class="fas fa-info-circle"></i> Для этого типа ответа варианты и баллы не задаются.';
            optionsArea.appendChild(info);
        }
        return optionsArea;
    }

    function refreshOptionsArea(card, newType) {
        const oldArea = card.querySelector('.test-editor__options-area');
        if (!oldArea) return;

        // Собираем текущие варианты (если есть)
        let currentOptions = [];
        const optionsList = card.querySelector('.options-list');
        if (optionsList) {
            optionsList.querySelectorAll('.option-item').forEach(item => {
                const text = item.querySelector('.option-text')?.value || '';
                const score = item.querySelector('.option-score')?.value || '';
                currentOptions.push({ text, score });
            });
        }

        const newArea = buildOptionsArea(newType, currentOptions);
        oldArea.replaceWith(newArea);
    }

    function extractQuestionData(card) {
        const id = card.dataset.questionId || 0;
        const text = card.querySelector('.question-text').value;
        const type = card.querySelector('.question-type').value;
        let image = card.dataset.imageBase64 || null;
        if (!image) {
            const imgTag = card.querySelector('.image-preview img');
            if (imgTag && imgTag.src && imgTag.src.startsWith('data:')) image = imgTag.src;
        }
        const answers = [];
        card.querySelectorAll('.option-item').forEach(opt => {
            const aId = opt.dataset.answerId || 0;
            const aText = opt.querySelector('.option-text').value;
            const aScore = opt.querySelector('.option-score').value;
            answers.push({ id: aId, text: aText, score: aScore });
        });
        return { id, text, type, image, answers };
    }

    // Создаёт новую карточку вопроса (чистую, без данных из БД)
    function createEmptyQuestionCard() {
        const card = document.createElement('div');
        card.className = 'test-editor__question-card';

        // Базовая структура
        card.innerHTML = `
            <div class="test-editor__question-head">
                <input type="text" class="question-text" placeholder="Текст вопроса">
                <select class="question-type">
                    <option value="radio">Один выбор</option>
                    <option value="checkbox">Несколько выборов</option>
                    <option value="select">Выпадающий список</option>
                    <option value="text">Короткий текст</option>
                    <option value="textarea">Длинный текст</option>
                </select>
                <button type="button" class="duplicate-question-btn" title="Копировать вопрос"><i class="fas fa-copy"></i></button>
                <button type="button" class="delete-question-btn"><i class="fas fa-trash"></i></button>
            </div>
            <div class="test-editor__image-zone">
                <div class="image-preview"><i class="fas fa-image"></i></div>
                <button type="button" class="upload-image-btn">Загрузить</button>
                <input type="file" class="image-file-input" accept="image/*" style="display:none">
            </div>
        `;

        // Добавляем область вариантов по умолчанию (radio с одним пустым вариантом)
        const defaultOptions = [{ text: '', score: '' }];
        const optionsArea = buildOptionsArea('radio', defaultOptions);
        card.appendChild(optionsArea);

        // Привязываем обработчики событий
        attachCardEventHandlers(card);
        return card;
    }

    // Создаёт карточку вопроса на основе существующих данных (для копирования или загрузки)
    function createQuestionCardFromData(data) {
        const card = document.createElement('div');
        card.className = 'test-editor__question-card';
        if (data.id) card.dataset.questionId = data.id;

        card.innerHTML = `
            <div class="test-editor__question-head">
                <input type="text" class="question-text" value="${escapeHtml(data.text)}" placeholder="Текст вопроса">
                <select class="question-type">
                    <option value="radio" ${data.type === 'radio' ? 'selected' : ''}>Один выбор</option>
                    <option value="checkbox" ${data.type === 'checkbox' ? 'selected' : ''}>Несколько выборов</option>
                    <option value="select" ${data.type === 'select' ? 'selected' : ''}>Выпадающий список</option>
                    <option value="text" ${data.type === 'text' ? 'selected' : ''}>Короткий текст</option>
                    <option value="textarea" ${data.type === 'textarea' ? 'selected' : ''}>Длинный текст</option>
                </select>
                <button type="button" class="duplicate-question-btn" title="Копировать вопрос"><i class="fas fa-copy"></i></button>
                <button type="button" class="delete-question-btn"><i class="fas fa-trash"></i></button>
            </div>
            <div class="test-editor__image-zone">
                <div class="image-preview">
                    ${data.image ? `<img src="${data.image}" width="70">` : '<i class="fas fa-image"></i>'}
                </div>
                <button type="button" class="upload-image-btn">Загрузить</button>
                <input type="file" class="image-file-input" accept="image/*" style="display:none">
            </div>
        `;

        // Область вариантов
        const optionsArea = buildOptionsArea(data.type, data.answers);
        card.appendChild(optionsArea);

        attachCardEventHandlers(card);
        return card;
    }

    // Общая функция для привязки событий к карточке (удаление, копирование, загрузка изображения, смена типа)
    function attachCardEventHandlers(card) {
        // Удаление вопроса
        const delBtn = card.querySelector('.delete-question-btn');
        if (delBtn) delBtn.addEventListener('click', () => card.remove());

        // Копирование вопроса
        const dupBtn = card.querySelector('.duplicate-question-btn');
        if (dupBtn) {
            dupBtn.addEventListener('click', () => {
                const data = extractQuestionData(card);
                const newCard = createQuestionCardFromData(data);
                card.after(newCard);
                newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        // Загрузка изображения
        const uploadBtn = card.querySelector('.upload-image-btn');
        const fileInput = card.querySelector('.image-file-input');
        const preview = card.querySelector('.image-preview');
        if (uploadBtn && fileInput && preview) {
            uploadBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        preview.innerHTML = `<img src="${ev.target.result}" width="70">`;
                        card.dataset.imageBase64 = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Смена типа вопроса
        const typeSelect = card.querySelector('.question-type');
        if (typeSelect) {
            typeSelect.addEventListener('change', (e) => {
                refreshOptionsArea(card, e.target.value);
            });
        }
    }

    // Добавление нового вопроса
    addBtn.addEventListener('click', () => {
        const newCard = createEmptyQuestionCard();
        container.appendChild(newCard);
        newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Отправка формы
    const form = document.getElementById('testEditorForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const questions = [];
            document.querySelectorAll('.test-editor__question-card').forEach(card => {
                questions.push(extractQuestionData(card));
            });
            const oldHidden = form.querySelector('input[name="questions"]');
            if (oldHidden) oldHidden.remove();
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'questions';
            hidden.value = JSON.stringify(questions);
            form.appendChild(hidden);
        });
    }

    // Инициализация уже существующих на странице карточек (загруженных из БД)
    document.querySelectorAll('.test-editor__question-card').forEach(card => {
        // Для существующих карточек нужно перестроить область вариантов, если она не соответствует типу
        const typeSelect = card.querySelector('.question-type');
        if (typeSelect) {
            const currentType = typeSelect.value;
            const optionsArea = card.querySelector('.test-editor__options-area');
            if (optionsArea) {
                // Проверяем, нужно ли перестроить
                const hasOptionsList = optionsArea.querySelector('.options-list');
                if ((TYPES_WITH_OPTIONS.includes(currentType) && !hasOptionsList) ||
                    (!TYPES_WITH_OPTIONS.includes(currentType) && hasOptionsList)) {
                    // Собираем существующие варианты
                    let currentOptions = [];
                    if (hasOptionsList) {
                        optionsArea.querySelectorAll('.option-item').forEach(item => {
                            const text = item.querySelector('.option-text')?.value || '';
                            const score = item.querySelector('.option-score')?.value || '';
                            currentOptions.push({ text, score });
                        });
                    }
                    const newArea = buildOptionsArea(currentType, currentOptions);
                    optionsArea.replaceWith(newArea);
                }
            } else {
                // Если нет области вариантов — создаём
                const newArea = buildOptionsArea(currentType, []);
                card.appendChild(newArea);
            }
        }
        attachCardEventHandlers(card);
    });
}