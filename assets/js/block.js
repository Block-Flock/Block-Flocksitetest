// Bloquear clic derecho
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// Bloquear teclas comunes para abrir la consola
document.addEventListener('keydown', function(e) {
    // F12
    if (e.keyCode === 123) {
        e.preventDefault();
    }

    // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S
    if (e.ctrlKey && (e.shiftKey && (e.key === 'I' || e.key === 'J') || e.key === 'U' || e.key === 'S')) {
        e.preventDefault();
    }

    // Ctrl+Shift+C (inspector)
    if (e.ctrlKey && e.shiftKey && e.key === 'C') {
        e.preventDefault();
    }
});