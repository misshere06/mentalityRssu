// Main application entry point
import { initHeader } from './modules/header';
import { initTestcreator } from './modules/testcreator';
import { initTesteditor } from './modules/testedit';
import { initUsersList } from './modules/userlist';
import './modules/testlistcard'; // <-- Просто импорт (выполнит IIFE-код)

document.addEventListener('DOMContentLoaded', () => {
    console.log('Application starting...');
    initHeader();

    // Определяем, какая страница открыта
    const isTestEditor = document.querySelector('.test-editor');
    if (isTestEditor) {
        console.log('Initializing test editor');
        initTesteditor();
    } else {
        console.log('Initializing test creator');
        initTestcreator();
    }

    // Инициализация компонента списка пользователей, если он присутствует на странице
    initUsersList();

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

export function domReady(callback) {
    if (document.readyState !== 'loading') {
        callback();
    } else {
        document.addEventListener('DOMContentLoaded', callback);
    }
}