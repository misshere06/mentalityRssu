export function initPsychoList() {
    const container = document.querySelector('.psycho-list-container');
    if (!container) return;

    const modal = document.getElementById('psychoModal');
    if (!modal) return;

    const overlay = modal.querySelector('.psycho-modal__overlay');
    const closeBtn = modal.querySelector('.psycho-modal__close');
    const photo = document.getElementById('psychoModalPhoto');
    const name = document.getElementById('psychoModalName');
    const about = document.getElementById('psychoModalAbout');
    const exp = document.getElementById('psychoModalExp');
    const bookBtn = document.getElementById('psychoModalBookBtn');

    function showModal(data) {
        if (photo) photo.src = data.photo || '';
        if (name) name.textContent = data.name;
        if (about) about.textContent = data.about || 'Описание отсутствует';
        if (exp) exp.textContent = data.experience ? `Стаж: ${data.experience}` : '';

        if (bookBtn) {
            if (data.accept === '1') {
                bookBtn.href = data.bookingUrl;
                bookBtn.classList.remove('disabled');
                bookBtn.textContent = 'Записаться';
            } else {
                bookBtn.removeAttribute('href');
                bookBtn.classList.add('disabled');
                bookBtn.textContent = 'Заявки не принимаются';
            }
        }

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    container.querySelectorAll('.psycho-card__detail-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            showModal(btn.dataset);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (overlay) overlay.addEventListener('click', hideModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') hideModal();
    });
}