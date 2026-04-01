// Sidebar module
export function initSidebar() {
  console.log('Initializing sidebar...');

  const sidebar = document.querySelector('.sidebar');
  const sidebarToggle = document.querySelector('.sidebar__toggle');
  const sidebarOverlay = document.querySelector('.sidebar-overlay');
  const mainContent = document.querySelector('.main-content');

  if (!sidebar) return;

  // Toggle sidebar
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-collapsed');
      if (mainContent) {
        mainContent.classList.toggle('expanded');
      }
    });
  }

  // Mobile sidebar toggle
  const mobileSidebarToggle = document.querySelector('.mobile-sidebar-toggle');
  if (mobileSidebarToggle && sidebarOverlay) {
    mobileSidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      sidebarOverlay.classList.toggle('d-block');
    });

    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('d-block');
    });
  }

  // Active menu item
  const menuItems = document.querySelectorAll('.sidebar__menu-item');
  menuItems.forEach(item => {
    item.addEventListener('click', () => {
      menuItems.forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });
}