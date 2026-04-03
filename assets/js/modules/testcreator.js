// assets/js/modules/testcreator.js
// Модуль конструктора тестов (визуальная часть, без сохранения)

export function initTestcreator() {
    // Контейнер для вопросов и кнопка добавления
    const container = document.getElementById('questionsContainer');
    const addButton = document.getElementById('addQuestionBtn');

    // Если на странице нет нужных элементов — конструктор не инициализируем
    if (!container || !addButton) return;

    let questionCounter = 0;

    const TYPES_WITH_OPTIONS = ['radio', 'checkbox', 'select'];

    function getDefaultQuestionData() {
        return {
            id: Date.now() + questionCounter++,
            questionText: 'Новый вопрос',
            type: 'radio',
            imageUrl: null,
            options: ['Вариант 1'],
        };
    }

    function createOptionElement(optionValue = '') {
        const optionDiv = document.createElement('div');
        optionDiv.className = 'testcreator__option-item';
        const input = document.createElement('input');
        input.type = 'text';
        input.value = optionValue;
        input.placeholder = 'Вариант ответа';
        input.setAttribute('aria-label', 'текст варианта');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'testcreator__remove-option';
        removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
        removeBtn.title = 'Удалить вариант';
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            optionDiv.remove();
        });
        optionDiv.appendChild(input);
        optionDiv.appendChild(removeBtn);
        return optionDiv;
    }

    function buildOptionsArea(type, currentOptions = ['Вариант 1']) {
        const optionsWrapper = document.createElement('div');
        optionsWrapper.className = 'testcreator__options-area';

        if (TYPES_WITH_OPTIONS.includes(type)) {
            const titleDiv = document.createElement('div');
            titleDiv.className = 'testcreator__options-title';
            titleDiv.innerHTML = `<i class="fas fa-list-ul"></i> Варианты ответов (${type === 'radio' ? 'один выбор' : type === 'checkbox' ? 'несколько выборов' : 'выпадающий список'})`;

            const optionsList = document.createElement('div');
            optionsList.className = 'testcreator__options-list';
            currentOptions.forEach(opt => {
                optionsList.appendChild(createOptionElement(opt));
            });

            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'testcreator__add-option';
            addBtn.innerHTML = '<i class="fas fa-plus"></i> Добавить вариант';
            addBtn.addEventListener('click', () => {
                const newOption = createOptionElement('Новый вариант');
                optionsList.appendChild(newOption);
            });

            optionsWrapper.appendChild(titleDiv);
            optionsWrapper.appendChild(optionsList);
            optionsWrapper.appendChild(addBtn);
        } else {
            const infoBlock = document.createElement('div');
            infoBlock.className = 'testcreator__info-message';
            if (type === 'text') {
                infoBlock.innerHTML = '<i class="fas fa-font"></i> Тип "Короткий текст" — респондент введёт текст. Варианты ответов не требуются.';
            } else if (type === 'textarea') {
                infoBlock.innerHTML = '<i class="fas fa-paragraph"></i> Тип "Длинный текст" — многострочное поле. Варианты не нужны.';
            } else {
                infoBlock.innerHTML = '<i class="fas fa-info-circle"></i> Для этого типа ответа варианты не задаются.';
            }
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

        if (initialImage && typeof initialImage === 'string') {
            containerDiv.setAttribute('data-image-data', initialImage);
        }
        return containerDiv;
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
        });

        head.appendChild(questionInputDiv);
        head.appendChild(typeDiv);
        head.appendChild(deleteBtn);
        body.appendChild(head);

        const imageBlock = createImageUploadBlock(data.imageUrl);
        body.appendChild(imageBlock);

        const optionsArea = buildOptionsArea(data.type, data.options);
        body.appendChild(optionsArea);

        card.appendChild(body);

        typeSelect.addEventListener('change', (e) => {
            const newType = e.target.value;
            let currentOpts = [];
            const optionsListDiv = card.querySelector('.testcreator__options-list');
            if (optionsListDiv) {
                const inputs = optionsListDiv.querySelectorAll('input');
                inputs.forEach(inp => currentOpts.push(inp.value));
            }
            if (currentOpts.length === 0 && TYPES_WITH_OPTIONS.includes(newType)) {
                currentOpts = ['Вариант 1'];
            }
            const oldOptionsArea = card.querySelector('.testcreator__options-area');
            if (oldOptionsArea) {
                const newOptionsAreaConstruct = buildOptionsArea(newType, currentOpts);
                oldOptionsArea.replaceWith(newOptionsAreaConstruct);
            }
        });

        return card;
    }

    function addNewQuestion() {
        const newQuestion = createQuestionElement();
        container.appendChild(newQuestion);
        newQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function initDemo() {
        const demoQuestion = createQuestionElement({
            id: Date.now() + 1000,
            questionText: 'Пример вопроса с вариантами',
            type: 'radio',
            imageUrl: null,
            options: ['Вариант A', 'Вариант B', 'Вариант C']
        });
        container.appendChild(demoQuestion);

        const textQuestionData = {
            id: Date.now() + 2000,
            questionText: 'Вопрос с текстовым ответом',
            type: 'text',
            imageUrl: null,
            options: []
        };
        const textQuestion = createQuestionElement(textQuestionData);
        container.appendChild(textQuestion);

        const checkboxData = {
            id: Date.now() + 3000,
            questionText: 'Выберите несколько вариантов',
            type: 'checkbox',
            imageUrl: null,
            options: ['Первый', 'Второй', 'Третий']
        };
        const checkboxQuestion = createQuestionElement(checkboxData);
        container.appendChild(checkboxQuestion);
    }

    // Назначение обработчика кнопки
    addButton.addEventListener('click', addNewQuestion);
    // Создание демо-вопросов
    initDemo();
}