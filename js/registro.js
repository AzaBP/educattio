document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const email = document.getElementById('email');
    const errorSpan = document.getElementById('password-error');
    const emailErrorSpan = document.createElement('span');
    emailErrorSpan.className = 'error-message';
    emailErrorSpan.style.display = 'none';
    emailErrorSpan.style.color = '#d32f2f';
    emailErrorSpan.style.fontSize = '0.75rem';
    email.insertAdjacentElement('afterend', emailErrorSpan);

    function validateEmail(emailValue) {
        const re = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
        return re.test(emailValue);
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            let hasError = false;

            // Validar contraseñas
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                errorSpan.style.display = 'block';
                confirmPassword.style.borderColor = '#ff4d4d';
                confirmPassword.focus();
                hasError = true;
            } else {
                errorSpan.style.display = 'none';
                confirmPassword.style.borderColor = '';
            }

            // Validar email
            if (!validateEmail(email.value)) {
                event.preventDefault();
                emailErrorSpan.textContent = 'Introduce un correo electrónico válido (ej: nombre@dominio.com)';
                emailErrorSpan.style.display = 'block';
                email.style.borderColor = '#ff4d4d';
                hasError = true;
            } else {
                emailErrorSpan.style.display = 'none';
                email.style.borderColor = '';
            }

            if (hasError) {
                // Evitar que se envíe
                return false;
            }
        });
    }
});