// Import only the function we need for this page
import {
    loginWithEmail, loginWithGoogle
} from './firebase/firebase-auth.js';

const togglePassword = document.querySelector('.input-group-text');
const password = document.querySelector('#password');
const icon = togglePassword.querySelector('i');

togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    // toggle the eye slash icon
    if (type === 'password') {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) return;

    const errorMessageDiv = document.getElementById('error-message');

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Add a loading state to the button
        const submitButton = loginForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Iniciando...`;

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        errorMessageDiv.style.display = 'none';

        try {
            await loginWithEmail(email, password);
        } catch (error) {
            showError(parseFirebaseError(error), submitButton);
        }
    });
});

function showError(message, button) {
    const errorBox = document.getElementById('error-message');
    errorBox.textContent = message;
    errorBox.style.display = 'block';

    button.disabled = false;
    button.innerHTML = 'Crear nueva cuenta';
}

// document.getElementById('google-login')
//     .addEventListener('click', async () => {
//         try {
//             await loginWithGoogle();
//         } catch (error) {
//             showError(parseFirebaseError(error));
//         }
// });