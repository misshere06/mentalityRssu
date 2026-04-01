// Main application entry point
import { initHeader } from './modules/header';

// Initialize all modules when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  console.log('Application starting...');

  // Initialize modules
  initHeader();

  console.log('Application initialized successfully!');
});

// Utility functions
export function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// DOM ready helper
export function domReady(callback) {
  if (document.readyState !== 'loading') {
    callback();
  } else {
    document.addEventListener('DOMContentLoaded', callback);
  }
}