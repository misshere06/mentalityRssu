export function initPsychoRequestList() {
    const containers = document.querySelectorAll('.js-psycho-request-list');
    if (containers.length === 0) return;

    containers.forEach(container => {
        if (container.dataset.initialized === 'true') return;
        container.dataset.initialized = 'true';

        const mode = container.dataset.mode;
        const iblockId = parseInt(container.dataset.iblockId, 10) || 0;
        const componentName = 'mn:psychorequest.list';

        const popup = container.querySelector('.js-request-popup');
        if (!popup) return;
        const overlay = popup.querySelector('.js-popup-overlay');
        const closeBtn = popup.querySelector('.js-popup-close');
        const popupContent = popup.querySelector('.js-popup-content');

        function closePopup() {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }
        function showPopup() {
            popup.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function escapeHtml(str) {
            if (!str) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(str).replace(/[&<>"']/g, m => map[m]);
        }

        function fillPopup(data) {
            if (!popupContent) return;
            let html = '<h3>Заявка #' + data.ID + '</h3>';
            if (mode === 'student') {
                html += '<div class="detail-row"><span>Психолог:</span> ' + escapeHtml(data.PSYCHOLOGIST.FULL_NAME) + '</div>';
                if (data.PSYCHOLOGIST.ABOUT) html += '<div class="detail-row"><span>О психологе:</span> ' + escapeHtml(data.PSYCHOLOGIST.ABOUT) + '</div>';
                if (data.PSYCHOLOGIST.EXPERIENCE) html += '<div class="detail-row"><span>Стаж:</span> ' + escapeHtml(data.PSYCHOLOGIST.EXPERIENCE) + '</div>';
            } else {
                html += '<div class="detail-row"><span>Студент:</span> ' + escapeHtml(data.STUDENT.FULL_NAME) + '</div>';
            }
            html += '<div class="detail-row"><span>Дата создания:</span> ' + escapeHtml(data.DATE_CREATE) + '</div>';
            html += '<div class="detail-row"><span>Желаемая дата:</span> ' + escapeHtml(data.PREFERRED_DATE) + '</div>';
            html += '<div class="detail-row"><span>Причина:</span> ' + escapeHtml(data.REASON) + '</div>';
            html += '<div class="detail-row"><span>Статус:</span> ' + escapeHtml(data.STATUS) + '</div>';

            if (mode === 'psycho' && data.CAN_EDIT) {
                html += '<div class="detail-actions"><label>Изменить статус:</label><select id="statusSelect" class="form-control">';
                ['new', 'accepted', 'completed', 'cancelled'].forEach(st => {
                    html += '<option value="' + st + '"' + (data.STATUS === st ? ' selected' : '') + '>' + st + '</option>';
                });
                html += '</select><button id="saveStatusBtn" class="btn btn-primary">Сохранить</button></div>';
            } else if (mode === 'student' && data.STATUS !== 'cancelled' && data.STATUS !== 'completed') {
                html += '<div class="detail-actions"><button id="cancelRequestBtn" class="btn btn-danger">Отменить заявку</button></div>';
            }
            popupContent.innerHTML = html;

            // Обработчики
            if (mode === 'psycho') {
                const saveBtn = document.getElementById('saveStatusBtn');
                if (saveBtn) {
                    saveBtn.onclick = () => {
                        const newStatus = document.getElementById('statusSelect').value;
                        BX.ajax.runComponentAction(componentName, 'updateStatus', {
                            mode: 'class',
                            data: { requestId: data.ID, newStatus, mode, iblockId },
                        }).then(res => {
                            if (res.data && res.data.success) { closePopup(); window.location.reload(); }
                            else alert(res.data?.error || 'Неизвестная ошибка');
                        }).catch(err => alert('Ошибка: ' + (err.errors?.[0]?.message || '')));
                    };
                }
            } else if (mode === 'student') {
                const cancelBtn = document.getElementById('cancelRequestBtn');
                if (cancelBtn) {
                    cancelBtn.onclick = () => {
                        if (confirm('Отменить заявку?')) {
                            BX.ajax.runComponentAction(componentName, 'cancelRequest', {
                                mode: 'class',
                                data: { requestId: data.ID, mode, iblockId },
                            }).then(res => {
                                if (res.data && res.data.success) { closePopup(); window.location.reload(); }
                                else alert(res.data?.error || 'Неизвестная ошибка');
                            }).catch(err => alert('Ошибка: ' + (err.errors?.[0]?.message || '')));
                        }
                    };
                }
            }
        }

        // Кнопки "Подробнее"
        container.querySelectorAll('.js-detail-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const requestId = this.dataset.requestId;
                BX.ajax.runComponentAction(componentName, 'getRequestDetail', {
                    mode: 'class',
                    data: { requestId, mode, iblockId },
                }).then(response => {
                    if (response.data && !response.data.error) {
                        fillPopup(response.data);
                        showPopup();
                    } else {
                        alert(response.data?.error || 'Заявка не найдена или нет доступа');
                    }
                }).catch(err => alert('Ошибка загрузки: ' + (err.errors?.[0]?.message || '')));
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', closePopup);
        if (overlay) overlay.addEventListener('click', closePopup);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && popup.style.display === 'block') closePopup();
        });
    });
}