/**
 * Модуль инициализации компонента списка пользователей с модальным окном тестов
 */
const COMPONENT_NAME = 'mn:users.list';

export function initUsersList() {
    const container = document.querySelector('.users-list-container');
    if (!container) return;

    if (container.dataset.usersListInitialized === 'true') return;
    container.dataset.usersListInitialized = 'true';

    const componentData = {
        signedParameters: container.dataset.signedParameters || '',
        componentPath: container.dataset.componentPath || ''
    };

    const modal = document.getElementById('userModal');
    if (!modal) return;

    const overlay = modal.querySelector('.user-modal-overlay');
    const closeBtn = modal.querySelector('.user-modal-close');
    const photoImg = document.getElementById('modalUserPhoto');
    const userNameEl = document.getElementById('modalUserName');
    const testsContainer = document.getElementById('modalTestsContainer');

    function showModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function setLoading(isLoading) {
        if (!testsContainer) return;
        if (isLoading) {
            testsContainer.innerHTML = '<div class="loader"></div>';
        }
    }

    async function loadUserTests(userId) {
        showModal();
        setLoading(true);

        try {
            if (typeof BX !== 'undefined' && BX.ajax && BX.ajax.runComponentAction) {
                console.log(`Отправка AJAX через ${COMPONENT_NAME}, userId:`, userId);
                const response = await BX.ajax.runComponentAction(COMPONENT_NAME, 'getUserTests', {
                    mode: 'class',
                    data: { userId },
                    signedParameters: componentData.signedParameters
                });
                console.log('Успешный ответ сервера:', response);
                fillModal(response.data);
            } else {
                console.warn('Bitrix JS API не загружен, используем fetch');
                const formData = new FormData();
                formData.append('action', 'getUserTests');
                formData.append('userId', userId);
                formData.append('signedParameters', componentData.signedParameters);
                formData.append('componentName', COMPONENT_NAME);
                formData.append('sessid', BX.bitrix_sessid ? BX.bitrix_sessid() : '');

                const response = await fetch(`/bitrix/services/main/ajax.php?mode=class&c=${encodeURIComponent(COMPONENT_NAME)}&action=getUserTests`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                console.log('Ответ fetch:', result);
                if (result.status === 'success') {
                    fillModal(result.data);
                } else {
                    console.error('Ошибка от сервера:', result.errors);
                    fillModal({ error: result.errors?.[0]?.message || 'Неизвестная ошибка' });
                }
            }
        } catch (error) {
            console.error('Объект ошибки от BX.ajax:', error);

            let errorMessage = 'Произошла ошибка при загрузке';
            if (error && typeof error === 'object') {
                if (Array.isArray(error.errors) && error.errors.length > 0) {
                    const firstError = error.errors[0];
                    if (typeof firstError === 'string') {
                        errorMessage = firstError;
                    } else if (firstError.message) {
                        errorMessage = firstError.message;
                    } else {
                        errorMessage = JSON.stringify(firstError);
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                } else if (error.status === 'error' && error.data && error.data.message) {
                    errorMessage = error.data.message;
                }
            }

            console.error('Сообщение ошибки для пользователя:', errorMessage);
            setLoading(false);
            fillModal({ error: errorMessage });
        }
    }

    function fillModal(data) {
        setLoading(false);

        if (data.error) {
            if (testsContainer) {
                testsContainer.innerHTML = `<p class="error-message">${escapeHtml(data.error)}</p>`;
            }
            return;
        }

        if (photoImg) {
            const photoUrl = data.userPhoto || '/local/templates/.default/images/no_photo.png';
            photoImg.src = photoUrl;
            photoImg.onerror = () => {
                photoImg.src = '/assets/img/avatar.png';
            };
        }
        if (userNameEl) {
            userNameEl.textContent = data.userName || 'Пользователь';
        }

        if (!testsContainer) return;

        if (!data.tests || data.tests.length === 0) {
            testsContainer.innerHTML = '<p>Нет пройденных тестов</p>';
            return;
        }

        let html = '';
        data.tests.forEach(test => {
            // Сервер возвращает STATUS = 'completed'
            const statusText = (test.STATUS === 'completed') ? 'Пройден' : test.STATUS;
            const statusClass = (test.STATUS === 'completed') ? 'status-passed' : 'status-unknown';
            html += `
                <div class="test-item">
                    <div class="test-name">${escapeHtml(test.NAME)}</div>
                    <div class="test-info">
                        <span class="test-status ${statusClass}">${escapeHtml(statusText)}</span>
                        <span class="test-score">Баллы: ${escapeHtml(test.SCORE)}</span>
                        <span class="test-date">${escapeHtml(test.DATE)}</span>
                    </div>
                </div>
            `;
        });
        testsContainer.innerHTML = html;
    }

    // Обработчики
    const buttons = container.querySelectorAll('.user-detail-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const userId = btn.dataset.userId;
            if (userId) {
                loadUserTests(userId);
            }
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (overlay) overlay.addEventListener('click', hideModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            hideModal();
        }
    });

    console.log('Users list component initialized');
}