document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const currentPage = normalizePage(window.location.pathname);
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const storedTheme = localStorage.getItem('bifrost-doc-theme');

    if (storedTheme === 'dark' || storedTheme === 'light') {
        root.dataset.theme = storedTheme;
    }

    updateThemeLabel();

    document.querySelectorAll('.nav-link').forEach((link) => {
        if (normalizePage(link.href) === currentPage) {
            link.classList.add('active');
        }

        link.addEventListener('click', () => {
            sidebar?.classList.remove('open');
        });
    });

    menuToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
    });

    themeToggle?.addEventListener('click', () => {
        const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = nextTheme;
        localStorage.setItem('bifrost-doc-theme', nextTheme);
        updateThemeLabel();
    });

    function updateThemeLabel() {
        if (!themeToggle) {
            return;
        }

        themeToggle.textContent = root.dataset.theme === 'dark'
            ? 'Tema claro'
            : 'Tema escuro';
    }

    function normalizePage(path) {
        const cleanPath = path.replace(/\\/g, '/').split('#')[0].split('?')[0];
        const parts = cleanPath.split('/').filter(Boolean);
        return parts.slice(-2).join('/') || 'index.html';
    }
});
