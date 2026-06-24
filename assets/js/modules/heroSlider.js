export function initHeroSlider() {
    const slider = document.querySelector('.hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.hero-slider__slide');
    const prevBtn = slider.querySelector('.hero-slider__btn--prev');
    const nextBtn = slider.querySelector('.hero-slider__btn--next');
    const dotsContainer = slider.querySelector('.hero-slider__dots');
    const captionContainer = slider.querySelector('.hero-slider__caption');

    // Массивы заголовков и описаний
    const captions = [
        { title: 'Психологический мониторинг студентов', text: 'Современная система оценки и поддержки психологического здоровья в РГСУ' },
        { title: 'Удобный личный кабинет', text: 'Управляйте своим профилем, проходите тестирования и получайте рекомендации' },
        { title: 'Детальная аналитика', text: 'Психологи получают удобные инструменты для анализа состояния студентов' }
    ];

    let currentIndex = 0;
    let interval;
    let touchStartX = 0;
    let touchEndX = 0;

    // Создаём точки
    slides.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.addEventListener('click', () => goTo(i));
        dotsContainer.appendChild(dot);
    });
    const dots = dotsContainer.querySelectorAll('span');

    function updateSlides() {
        slides.forEach((s, i) => s.classList.toggle('active', i === currentIndex));
        dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
        // Обновляем текст описания
        if (captionContainer && captions[currentIndex]) {
            captionContainer.innerHTML = `
        <h2>${captions[currentIndex].title}</h2>
        <p>${captions[currentIndex].text}</p>
      `;
        }
    }

    function goTo(index) {
        currentIndex = (index + slides.length) % slides.length;
        updateSlides();
    }

    function next() { goTo(currentIndex + 1); }
    function prev() { goTo(currentIndex - 1); }

    // Кнопки (на десктопе)
    if (prevBtn) prevBtn.addEventListener('click', () => { prev(); resetInterval(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { next(); resetInterval(); });

    // Поддержка свайпов
    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX < touchStartX - swipeThreshold) {
            next();
            resetInterval();
        }
        if (touchEndX > touchStartX + swipeThreshold) {
            prev();
            resetInterval();
        }
    }

    // Автопрокрутка
    function startInterval() { interval = setInterval(next, 5000); }
    function resetInterval() { clearInterval(interval); startInterval(); }

    // Клавиатура
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') { prev(); resetInterval(); }
        if (e.key === 'ArrowRight') { next(); resetInterval(); }
    });

    updateSlides();
    startInterval();
}