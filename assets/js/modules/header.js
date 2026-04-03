// Header module - Burger menu and search functionality
export function initHeader() {
    // Burger menu
    const burgerToggle = document.querySelector('.header__burger-menu-toggle');
    const burgerMenu = document.querySelector('.burger-menu');
    const burgerClose = document.querySelector('.burger-menu__close');

    if (burgerToggle && burgerMenu && burgerClose) {
        const openMenu = () => {
            burgerMenu.classList.add('burger-menu--active');
            document.body.style.overflow = 'hidden';
        };

        const closeMenu = () => {
            burgerMenu.classList.remove('burger-menu--active');
            document.body.style.overflow = '';
        };

        burgerToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (burgerMenu.classList.contains('burger-menu--active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        burgerClose.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeMenu();
        });

        document.addEventListener('click', (e) => {
            if (burgerMenu.classList.contains('burger-menu--active') &&
                !burgerMenu.contains(e.target) &&
                e.target !== burgerToggle) {
                closeMenu();
            }
        });
    }

    // Search functionality
    const searchButton = document.querySelector('.header__search-button');
    const searchInput = document.querySelector('.header__search-input');
    const searchClose = document.querySelector('.header__search-close');
    const headerNav = document.querySelector('.header__nav');

    if (searchButton && searchInput && searchClose && headerNav) {
        const showSearch = () => {
            searchInput.style.display = 'block';
            searchClose.style.display = 'block';
            headerNav.style.display = 'none';
            searchInput.focus();
        };

        const hideSearch = () => {
            searchInput.style.display = 'none';
            searchClose.style.display = 'none';
            headerNav.style.display = 'flex';
            searchInput.value = '';
        };

        const performSearch = () => {
            const query = searchInput.value.trim();
            if (query) {
                // Здесь можно реализовать реальный поиск
                console.log(`Search: ${query}`);
            }
        };

        searchButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (searchInput.style.display === 'none' || !searchInput.style.display) {
                showSearch();
            } else {
                performSearch();
            }
        });

        searchClose.addEventListener('click', (e) => {
            e.preventDefault();
            hideSearch();
        });

        document.addEventListener('click', (e) => {
            if (searchInput.style.display === 'block' &&
                !searchButton.contains(e.target) &&
                !searchInput.contains(e.target) &&
                !searchClose.contains(e.target)) {
                hideSearch();
            }
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
}