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

    document.querySelectorAll('pre code').forEach((block) => {
        const language = Array.from(block.classList).find((className) => className.startsWith('language-'));
        if (language === 'language-php') {
            block.innerHTML = highlightPhp(block.textContent || '');
            return;
        }

        if (language === 'language-bash' || language === 'language-text') {
            block.innerHTML = highlightShell(block.textContent || '');
        }
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

    function highlightPhp(code) {
        const keywords = new Set([
            'namespace', 'use', 'final', 'readonly', 'class', 'interface', 'enum',
            'abstract', 'public', 'private', 'protected', 'static', 'function',
            'return', 'throw', 'new', 'if', 'else', 'foreach', 'as', 'null',
            'true', 'false', 'self', 'parent', 'extends', 'implements',
        ]);
        const types = new Set(['string', 'int', 'bool', 'float', 'array', 'mixed', 'void', 'object', 'callable']);
        const pattern = /(<\?php|\?>|#\[[\s\S]*?\]|\/\/[^\n]*|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|\$[A-Za-z_][A-Za-z0-9_]*|->|::|=>|[=?|&]|\b\d+\b|\b[A-Za-z_][A-Za-z0-9_\\]*\b)/g;

        return highlightTokens(code, pattern, (token, offset) => {
            if (token === '<?php' || token === '?>' || keywords.has(token)) {
                return 'token-keyword';
            }

            if (types.has(token) || /^[A-Z][A-Za-z0-9_\\]*$/.test(token)) {
                return 'token-type';
            }

            if (token.startsWith('#[')) {
                return 'token-attribute';
            }

            if (token.startsWith('//')) {
                return 'token-comment';
            }

            if (token.startsWith("'") || token.startsWith('"')) {
                return 'token-string';
            }

            if (token.startsWith('$')) {
                return 'token-variable';
            }

            if (/^\d+$/.test(token)) {
                return 'token-number';
            }

            if (/^(->|::|=>|=|\?|\||&)$/.test(token)) {
                return 'token-operator';
            }

            const next = code.slice(offset + token.length).match(/^\s*\(/);
            return next ? 'token-function' : '';
        });
    }

    function highlightShell(code) {
        const commands = new Set(['composer', 'docker', 'php', 'curl', 'cd', 'mkdir', 'cp']);
        const pattern = /(#.*$|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|--[A-Za-z0-9-]+|\b\d+\b|\b[A-Za-z_][A-Za-z0-9_-]*\b)/gm;

        return highlightTokens(code, pattern, (token) => {
            if (token.startsWith('#')) {
                return 'token-comment';
            }

            if (token.startsWith("'") || token.startsWith('"')) {
                return 'token-string';
            }

            if (token.startsWith('--')) {
                return 'token-attribute';
            }

            if (/^\d+$/.test(token)) {
                return 'token-number';
            }

            return commands.has(token) ? 'token-keyword' : '';
        });
    }

    function highlightTokens(code, pattern, classifier) {
        let html = '';
        let cursor = 0;

        for (const match of code.matchAll(pattern)) {
            const token = match[0];
            const offset = match.index || 0;
            html += escapeHtml(code.slice(cursor, offset));

            const className = classifier(token, offset);
            html += className === ''
                ? escapeHtml(token)
                : `<span class="${className}">${escapeHtml(token)}</span>`;
            cursor = offset + token.length;
        }

        return html + escapeHtml(code.slice(cursor));
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
});
