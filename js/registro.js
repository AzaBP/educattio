// Esperamos a que el DOM esté cargado para evitar errores
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const errorSpan = document.getElementById('password-error');

    if (form) {
        form.addEventListener('submit', function(event) {
            // Comprobamos si las contraseñas coinciden
            if (password.value !== confirmPassword.value) {
                event.preventDefault(); // Detiene el envío del formulario al PHP
                
                // Efectos visuales de error
                errorSpan.style.display = 'block';
                confirmPassword.style.borderColor = '#ff4d4d';
                confirmPassword.focus();
            } else {
                errorSpan.style.display = 'none';
                confirmPassword.style.borderColor = '';
            }
        });
    }
});