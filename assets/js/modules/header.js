// Header module - Burger menu functionality
export function initHeader() {
  console.log('=== HEADER MODULE INITIALIZED ===');
  console.log('Header module loaded and ready');

  // Burger menu toggle
  const burgerToggle = document.querySelector('.header__burger-menu-toggle');
  const burgerMenu = document.querySelector('.burger-menu');
  const burgerClose = document.querySelector('.burger-menu__close');

  console.log('Checking burger menu elements...');
  console.log('- Burger toggle:', burgerToggle);
  console.log('- Burger menu:', burgerMenu);
  console.log('- Burger close:', burgerClose);

  if (burgerToggle && burgerMenu && burgerClose) {
    console.log('✅ All burger menu elements found - setting up event listeners');

    // Add visual indicator that button is ready
    burgerToggle.style.outline = '2px solid lime';
    setTimeout(() => {
      burgerToggle.style.outline = '';
    }, 2000);

    // Toggle burger menu (open if closed, close if open)
    burgerToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      e.preventDefault();

      if (burgerMenu.classList.contains('burger-menu--active')) {
        console.log('Closing burger menu (toggle click)');
        burgerMenu.classList.remove('burger-menu--active');
        document.body.style.overflow = '';
      } else {
        console.log('Opening burger menu (toggle click)');
        burgerMenu.classList.add('burger-menu--active');
        document.body.style.overflow = 'hidden';
      }
    });

    // Close burger menu via close button
    burgerClose.addEventListener('click', function(e) {
      e.stopPropagation();
      e.preventDefault();
      console.log('Closing burger menu (close button)');
      burgerMenu.classList.remove('burger-menu--active');
      document.body.style.overflow = '';
    });

    // Close burger menu when clicking outside
    document.addEventListener('click', function(e) {
      if (burgerMenu.classList.contains('burger-menu--active') && !burgerMenu.contains(e.target) && e.target !== burgerToggle) {
        e.preventDefault();
        console.log('Closing burger menu (outside click)');
        burgerMenu.classList.remove('burger-menu--active');
        document.body.style.overflow = '';
      }
    });
  } else {
    console.log('Burger menu elements not found');
  }

  // Search functionality
  const searchButton = document.querySelector('.header__search-button');
  const searchInput = document.querySelector('.header__search-input');
  const searchClose = document.querySelector('.header__search-close');
  const headerNav = document.querySelector('.header__nav');

  if (searchButton && searchInput && searchClose && headerNav) {
    console.log('✅ Search elements found - setting up search functionality');

    // Toggle search input visibility
    searchButton.addEventListener('click', function(e) {
      e.preventDefault();

      if (searchInput.style.display === 'none' || !searchInput.style.display) {
        // Show search input and hide nav
        searchInput.style.display = 'block';
        searchClose.style.display = 'block';
        headerNav.style.display = 'none';
        searchInput.focus();
        console.log('Search input shown, nav hidden');
      } else {
        // Trigger search event
        if (searchInput.value.trim()) {
          console.log('Triggering search event with query:', searchInput.value);
          // In real implementation, this would trigger actual search
          alert('Search event triggered with query: ' + searchInput.value);
        }
      }
    });

    // Close search input
    searchClose.addEventListener('click', function(e) {
      e.preventDefault();
      searchInput.style.display = 'none';
      searchClose.style.display = 'none';
      headerNav.style.display = 'flex';
      searchInput.value = '';
      console.log('Search input hidden, nav shown');
    });

    // Close search when clicking outside
    document.addEventListener('click', function(e) {
      if (searchInput.style.display === 'block' &&
          !searchButton.contains(e.target) &&
          !searchInput.contains(e.target) &&
          !searchClose.contains(e.target)) {
        searchInput.style.display = 'none';
        searchClose.style.display = 'none';
        headerNav.style.display = 'flex';
        searchInput.value = '';
        console.log('Search closed by outside click');
      }
    });

    // Handle Enter key in search input
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (searchInput.value.trim()) {
          console.log('Search triggered by Enter key');
          alert('Search event triggered with query: ' + searchInput.value);
        }
      }
    });
  } else {
    console.log('⚠️ Search elements not found');
  }
}
