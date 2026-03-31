// Header module
export function initHeader() {
  console.log('Initializing header...');

  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector('.header__mobile-toggle');
  const headerNav = document.querySelector('.header__nav');

  if (mobileMenuToggle && headerNav) {
    mobileMenuToggle.addEventListener('click', () => {
      headerNav.classList.toggle('d-block');
      headerNav.classList.toggle('d-none');
    });
  }

  // User menu dropdown
  const userMenuToggle = document.querySelector('.header__user-menu');
  const userMenuDropdown = document.querySelector('.header__user-dropdown');

  if (userMenuToggle && userMenuDropdown) {
    userMenuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      userMenuDropdown.classList.toggle('d-block');
    });

    document.addEventListener('click', () => {
      userMenuDropdown.classList.remove('d-block');
    });
  }
}