document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const body = document.body;
    
    // Check local storage for saved theme
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'light') {
        body.classList.add('light-mode');
        if (themeToggleBtn) {
            if (themeToggleBtn.tagName === 'INPUT' && themeToggleBtn.type === 'checkbox') {
                themeToggleBtn.checked = true;
            } else {
                themeToggleBtn.innerText = 'Switch to Dark Mode';
            }
        }
    } else {
        // Default is dark mode
        if (themeToggleBtn) {
            if (themeToggleBtn.tagName === 'INPUT' && themeToggleBtn.type === 'checkbox') {
                themeToggleBtn.checked = false;
            } else {
                themeToggleBtn.innerText = 'Switch to Light Mode';
            }
        }
    }

    // Toggle theme function
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', (e) => {
            // If it's a link acting as a button, prevent navigation if it's just #
            if (themeToggleBtn.tagName === 'A' && themeToggleBtn.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            body.classList.toggle('light-mode');
            
            const isLight = body.classList.contains('light-mode');
            const theme = isLight ? 'light' : 'dark';
            localStorage.setItem('theme', theme);

            // Update button text if it's a simple button
            if (themeToggleBtn.tagName !== 'INPUT' && themeToggleBtn.innerText.includes('Mode')) {
                themeToggleBtn.innerText = isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode';
            }
        });
    }
});
