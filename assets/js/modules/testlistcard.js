;(function() {
    'use strict';

    function initTestListCards() {
        const container = document.querySelector('.js-test-list-cards');
        if (!container) return;

        // --- Переключение вида сетка/список ---
        const viewButtons = container.querySelectorAll('.js-view-btn');
        const itemsContainer = container.querySelector('.js-items-container');
        if (itemsContainer) {
            const STORAGE_KEY = 'test_list_view_mode';

            function applyView(view) {
                itemsContainer.classList.toggle('view-list', view === 'list');
                viewButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            function setView(view) {
                localStorage.setItem(STORAGE_KEY, view);
                applyView(view);
            }

            const savedView = localStorage.getItem(STORAGE_KEY) || 'grid';
            applyView(savedView);

            viewButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    setView(this.dataset.view);
                });
            });
        }

        // --- Попап с подробным описанием ---
        const popup = container.querySelector('.js-test-popup');
        if (!popup) return;

        const popupTitle = popup.querySelector('.js-popup-title');
        const popupDesc = popup.querySelector('.js-popup-description');
        const popupInstr = popup.querySelector('.js-popup-instruction');
        const closeButtons = popup.querySelectorAll('.js-popup-close');

        function closePopup() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }

        function openPopup(titleText, description, instruction) {
            if (popupTitle) popupTitle.textContent = titleText;
            if (popupDesc) popupDesc.innerHTML = description || '';
            if (popupInstr) popupInstr.innerHTML = instruction || '';
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Закрытие по клику на оверлей или крестик
        closeButtons.forEach(btn => {
            btn.addEventListener('click', closePopup);
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popup.style.display === 'flex') {
                closePopup();
            }
        });

        // Обработчики кнопок "Подробнее"
        const detailButtons = container.querySelectorAll('.js-show-detail');
        detailButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const description = this.dataset.description || '';
                const instruction = this.dataset.instruction || '';
                // Заголовок берём из названия теста — можно из соседнего h2
                const itemTitle = this.closest('.test-list-cards__item')
                    ?.querySelector('.test-list-cards__item-title a')
                    ?.textContent || 'Подробнее';
                openPopup(itemTitle, description, instruction);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTestListCards);
    } else {
        initTestListCards();
    }
})();