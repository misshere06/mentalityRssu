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
        const popupStatus = popup.querySelector('.js-popup-status');
        const closeButtons = popup.querySelectorAll('.js-popup-close');

        function closePopup() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }

        function openPopup(titleText, description, instruction, isCompleted, completedDate) {
            if (popupTitle) popupTitle.textContent = titleText;
            if (popupDesc) popupDesc.innerHTML = description || '';
            if (popupInstr) popupInstr.innerHTML = instruction || '';

            if (popupStatus) {
                if (isCompleted) {
                    const formattedDate = completedDate
                        ? new Date(completedDate.replace(' ', 'T')).toLocaleString('ru-RU', {
                            day: '2-digit', month: '2-digit', year: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        })
                        : '';
                    popupStatus.innerHTML = `<strong style="color: ${'#28a745'};">Пройден</strong>` +
                        (formattedDate ? ` — ${formattedDate}` : '');
                } else {
                    popupStatus.innerHTML = '<strong style="color: #dc3545;">Не пройден</strong>';
                }
            }

            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        closeButtons.forEach(btn => btn.addEventListener('click', closePopup));

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popup.style.display === 'flex') {
                closePopup();
            }
        });

        const detailButtons = container.querySelectorAll('.js-show-detail');
        detailButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const description = this.dataset.description || '';
                const instruction = this.dataset.instruction || '';
                const isCompleted = this.dataset.completed === '1';
                const completedDate = this.dataset.completedDate || '';

                const itemTitle = this.closest('.test-list-cards__item')
                    ?.querySelector('.test-list-cards__item-title a')
                    ?.textContent || 'Подробнее';

                openPopup(itemTitle, description, instruction, isCompleted, completedDate);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTestListCards);
    } else {
        initTestListCards();
    }
})();