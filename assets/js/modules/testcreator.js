// assets/js/modules/testcreator.js
export function initTestcreator() {
    const container = document.getElementById('questionsContainer');
    const addButton = document.getElementById('addQuestionBtn');
    const addButtonFooter = document.getElementById('addQuestionBtnFooter');
    const scrollBtn = document.getElementById('scrollTopBtn');
    const counterSpan = document.getElementById('counterValue');
    const createBtn = document.getElementById('createTestBtn');

    if (!container || (!addButton && !addButtonFooter)) return;

    let questionCounter = 0;
    const TYPES_WITH_OPTIONS = ['radio', 'checkbox', 'select'];

    function updateQuestionCount() {
        if (!counterSpan) return;
        const count = document.querySelectorAll('.testcreator__question-card').length;
        counterSpan.textContent = count;
    }

    if (scrollBtn) {
        scrollBtn.addEventListener('click', () => {
            const header = document.querySelector('.testcreator__header');
            if (header) header.scrollIntoView({ behavior: 'smooth', block: 'start' });
            else window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    function getDefaultQuestionData() {
        return {
            id: Date.now() + questionCounter++,
            questionText: 'Новый вопрос',
            type: 'radio',
            imageUrl: null,
            options: [{ text: 'Вариант 1', score: 0 }],
        };
    }

    function createOptionElement(option = { text: '', score: 0 }) {
        const optionDiv = document.createElement('div');
        optionDiv.className = 'testcreator__option-item';
        const textInput = document.createElement('input');
        textInput.type = 'text';
        textInput.value = option.text;
        textInput.placeholder = 'Текст варианта';
        const scoreInput = document.createElement('input');
        scoreInput.type = 'number';
        scoreInput.step = '0.5';
        scoreInput.value = option.score;
        scoreInput.placeholder = 'Баллы';
        scoreInput.className = 'testcreator__option-score';
        scoreInput.min = 0;
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'testcreator__remove-option';
        removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
        removeBtn.title = 'Удалить вариант';
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            optionDiv.remove();
        });
        optionDiv.appendChild(textInput);
        optionDiv.appendChild(scoreInput);
        optionDiv.appendChild(removeBtn);
        return optionDiv;
    }

    function buildOptionsArea(type, currentOptions = [{ text: 'Вариант 1', score: 0 }]) {
        const optionsWrapper = document.createElement('div');
        optionsWrapper.className = 'testcreator__options-area';
        if (TYPES_WITH_OPTIONS.includes(type)) {
            const titleDiv = document.createElement('div');
            titleDiv.className = 'testcreator__options-title';
            titleDiv.innerHTML = `<i class="fas fa-list-ul"></i> Варианты ответов (${type === 'radio' ? 'один выбор' : type === 'checkbox' ? 'несколько выборов' : 'выпадающий список'}) + баллы`;
            const optionsList = document.createElement('div');
            optionsList.className = 'testcreator__options-list';
            currentOptions.forEach(opt => optionsList.appendChild(createOptionElement(opt)));
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'testcreator__add-option';
            addBtn.innerHTML = '<i class="fas fa-plus"></i> Добавить вариант';
            addBtn.addEventListener('click', () => {
                optionsList.appendChild(createOptionElement({ text: 'Новый вариант', score: 0 }));
            });
            optionsWrapper.appendChild(titleDiv);
            optionsWrapper.appendChild(optionsList);
            optionsWrapper.appendChild(addBtn);
        } else {
            const infoBlock = document.createElement('div');
            infoBlock.className = 'testcreator__info-message';
            if (type === 'text') infoBlock.innerHTML = '<i class="fas fa-font"></i> Тип "Короткий текст" — респондент введёт текст. Баллы не присваиваются.';
            else if (type === 'textarea') infoBlock.innerHTML = '<i class="fas fa-paragraph"></i> Тип "Длинный текст" — многострочное поле. Баллы не нужны.';
            else infoBlock.innerHTML = '<i class="fas fa-info-circle"></i> Для этого типа ответа варианты и баллы не задаются.';
            optionsWrapper.appendChild(infoBlock);
        }
        return optionsWrapper;
    }

    function createImageUploadBlock(initialImage = null) {
        const containerDiv = document.createElement('div');
        containerDiv.className = 'testcreator__image-zone';
        const previewContainer = document.createElement('div');
        if (initialImage && typeof initialImage === 'string') {
            const img = document.createElement('img');
            img.src = initialImage;
            img.className = 'testcreator__image-preview';
            previewContainer.appendChild(img);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'testcreator__image-placeholder';
            placeholder.innerHTML = '<i class="fas fa-image"></i>';
            previewContainer.appendChild(placeholder);
        }
        const buttonsDiv = document.createElement('div');
        buttonsDiv.className = 'testcreator__image-buttons';
        const uploadLabel = document.createElement('label');
        uploadLabel.className = 'testcreator__image-btn';
        uploadLabel.innerHTML = '<i class="fas fa-upload"></i> Загрузить';
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const imgData = ev.target.result;
                    previewContainer.innerHTML = '';
                    const newImg = document.createElement('img');
                    newImg.src = imgData;
                    newImg.className = 'testcreator__image-preview';
                    previewContainer.appendChild(newImg);
                    containerDiv.setAttribute('data-image-data', imgData);
                };
                reader.readAsDataURL(file);
            }
            fileInput.value = '';
        });
        uploadLabel.appendChild(fileInput);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'testcreator__image-btn';
        removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Удалить';
        removeBtn.addEventListener('click', () => {
            previewContainer.innerHTML = '';
            const placeholder = document.createElement('div');
            placeholder.className = 'testcreator__image-placeholder';
            placeholder.innerHTML = '<i class="fas fa-image"></i>';
            previewContainer.appendChild(placeholder);
            containerDiv.removeAttribute('data-image-data');
        });
        buttonsDiv.appendChild(uploadLabel);
        buttonsDiv.appendChild(removeBtn);
        containerDiv.appendChild(previewContainer);
        containerDiv.appendChild(buttonsDiv);
        if (initialImage && typeof initialImage === 'string') containerDiv.setAttribute('data-image-data', initialImage);
        return containerDiv;
    }

    function extractQuestionData(card) {
        const questionText = card.querySelector('.testcreator__question-input input')?.value || '';
        const typeSelect = card.querySelector('.testcreator__type-select select');
        const type = typeSelect ? typeSelect.value : 'radio';
        const imageZone = card.querySelector('.testcreator__image-zone');
        let imageUrl = null;
        if (imageZone && imageZone.hasAttribute('data-image-data')) imageUrl = imageZone.getAttribute('data-image-data');
        else {
            const img = imageZone?.querySelector('.testcreator__image-preview');
            if (img && img.src && img.src.startsWith('data:')) imageUrl = img.src;
        }
        const options = [];
        card.querySelectorAll('.testcreator__option-item').forEach(item => {
            const textInput = item.querySelector('input[type="text"]');
            const scoreInput = item.querySelector('.testcreator__option-score');
            if (textInput) options.push({ text: textInput.value, score: scoreInput ? parseFloat(scoreInput.value) || 0 : 0 });
        });
        return { questionText, type, imageUrl, options };
    }

    function createQuestionElement(questionData = null) {
        const data = questionData || getDefaultQuestionData();
        const card = document.createElement('div');
        card.className = 'testcreator__question-card';
        card.setAttribute('data-question-id', data.id);
        const body = document.createElement('div');
        body.className = 'testcreator__question-body';
        const head = document.createElement('div');
        head.className = 'testcreator__question-head';
        const questionInputDiv = document.createElement('div');
        questionInputDiv.className = 'testcreator__question-input';
        const questionField = document.createElement('input');
        questionField.type = 'text';
        questionField.value = data.questionText;
        questionField.placeholder = 'Введите текст вопроса...';
        questionInputDiv.appendChild(questionField);
        const typeDiv = document.createElement('div');
        typeDiv.className = 'testcreator__type-select';
        const typeSelect = document.createElement('select');
        const types = [
            { value: 'radio', label: 'Один выбор (Radio)' },
            { value: 'checkbox', label: 'Несколько выборов (Чекбоксы)' },
            { value: 'select', label: 'Выпадающий список' },
            { value: 'text', label: 'Короткий текст' },
            { value: 'textarea', label: 'Длинный текст (абзац)' }
        ];
        types.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.value;
            opt.textContent = t.label;
            if (t.value === data.type) opt.selected = true;
            typeSelect.appendChild(opt);
        });
        typeDiv.appendChild(typeSelect);
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'testcreator__delete-question';
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
        deleteBtn.title = 'Удалить вопрос';
        deleteBtn.addEventListener('click', () => {
            card.remove();
            updateQuestionCount();
        });
        const duplicateBtn = document.createElement('button');
        duplicateBtn.className = 'testcreator__duplicate-question';
        duplicateBtn.innerHTML = '<i class="far fa-copy"></i>';
        duplicateBtn.title = 'Копировать вопрос';
        duplicateBtn.addEventListener('click', () => {
            const currentData = extractQuestionData(card);
            const newQuestion = createQuestionElement({ ...currentData, id: Date.now() + questionCounter++ });
            card.after(newQuestion);
            updateQuestionCount();
            newQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        head.appendChild(questionInputDiv);
        head.appendChild(typeDiv);
        head.appendChild(duplicateBtn);
        head.appendChild(deleteBtn);
        body.appendChild(head);
        const imageBlock = createImageUploadBlock(data.imageUrl);
        body.appendChild(imageBlock);
        let optionsForArea = data.options;
        if (!optionsForArea || optionsForArea.length === 0) {
            if (TYPES_WITH_OPTIONS.includes(data.type)) optionsForArea = [{ text: 'Вариант 1', score: 0 }];
            else optionsForArea = [];
        }
        const optionsArea = buildOptionsArea(data.type, optionsForArea);
        body.appendChild(optionsArea);
        card.appendChild(body);
        typeSelect.addEventListener('change', (e) => {
            const newType = e.target.value;
            let currentOptions = [];
            const optionsListDiv = card.querySelector('.testcreator__options-list');
            if (optionsListDiv) {
                const items = optionsListDiv.querySelectorAll('.testcreator__option-item');
                items.forEach(item => {
                    const textInput = item.querySelector('input[type="text"]');
                    const scoreInput = item.querySelector('.testcreator__option-score');
                    if (textInput) currentOptions.push({ text: textInput.value, score: scoreInput ? parseFloat(scoreInput.value) || 0 : 0 });
                });
            }
            if (currentOptions.length === 0 && TYPES_WITH_OPTIONS.includes(newType)) currentOptions = [{ text: 'Вариант 1', score: 0 }];
            const oldOptionsArea = card.querySelector('.testcreator__options-area');
            if (oldOptionsArea) oldOptionsArea.replaceWith(buildOptionsArea(newType, currentOptions));
        });
        return card;
    }

    function addNewQuestion() {
        const newQuestion = createQuestionElement();
        container.appendChild(newQuestion);
        updateQuestionCount();
        newQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function initDemo() {
        const demoQuestion = createQuestionElement({
            id: Date.now() + 1000,
            questionText: 'Пример вопроса с баллами',
            type: 'radio',
            imageUrl: null,
            options: [{ text: 'Вариант A', score: 2 }, { text: 'Вариант B', score: 5 }, { text: 'Вариант C', score: 0 }]
        });
        container.appendChild(demoQuestion);
        const textQuestionData = {
            id: Date.now() + 2000,
            questionText: 'Вопрос с текстовым ответом (без баллов)',
            type: 'text',
            imageUrl: null,
            options: []
        };
        container.appendChild(createQuestionElement(textQuestionData));
        const checkboxData = {
            id: Date.now() + 3000,
            questionText: 'Выберите несколько вариантов (баллы суммируются)',
            type: 'checkbox',
            imageUrl: null,
            options: [{ text: 'Первый', score: 1 }, { text: 'Второй', score: 2 }, { text: 'Третий', score: 3 }]
        };
        container.appendChild(createQuestionElement(checkboxData));
        updateQuestionCount();
    }

    if (addButton) addButton.addEventListener('click', addNewQuestion);
    if (addButtonFooter) addButtonFooter.addEventListener('click', addNewQuestion);
    initDemo();

    // Обработчик сохранения теста
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            const testName = document.getElementById('testName').value.trim();
            const description = document.getElementById('testDescription').value.trim();
            const categoryId = document.getElementById('categoryId').value;
            if (!testName) { alert('Введите название теста'); return; }
            if (!categoryId || categoryId == '0') { alert('Выберите категорию'); return; }
            const questions = [];
            document.querySelectorAll('.testcreator__question-card').forEach(card => {
                const qText = card.querySelector('.testcreator__question-input input')?.value || '';
                const type = card.querySelector('.testcreator__type-select select')?.value;
                let image = null;
                const imgPreview = card.querySelector('.testcreator__image-preview');
                if (imgPreview && imgPreview.src && imgPreview.src.startsWith('data:')) image = imgPreview.src;
                const options = [];
                card.querySelectorAll('.testcreator__option-item').forEach(opt => {
                    const optText = opt.querySelector('input[type="text"]')?.value || '';
                    const score = parseFloat(opt.querySelector('.testcreator__option-score')?.value) || 0;
                    if (optText) options.push({ text: optText, score: score });
                });
                questions.push({ text: qText, type: type, image: image, options: options });
            });
            if (questions.length === 0) { alert('Добавьте хотя бы один вопрос'); return; }
            if (typeof BX !== 'undefined' && BX.ajax) {
                BX.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: { action: 'saveTest', testName: testName, description: description, categoryId: categoryId, questionsData: JSON.stringify(questions) },
                    onsuccess: function(response) {
                        let data;
                        try { data = JSON.parse(response); } catch(e) { data = { success: false, error: 'Ошибка ответа сервера' }; }
                        if (data.success) {
                            alert('Тест успешно создан! ID теста: ' + data.testId);
                            location.reload();
                        } else {
                            alert('Ошибка: ' + data.error);
                        }
                    },
                    onfailure: function() { alert('Ошибка соединения'); }
                });
            } else {
                alert('Ошибка: не загружен модуль ajax Битрикс. Добавьте CJSCore::Init(["ajax"]); в шаблон компонента.');
            }
        });
    }
}