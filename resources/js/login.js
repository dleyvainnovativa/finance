// Import only the function we need for this page
import {
    loginWithEmail, loginWithGoogle
} from './firebase/firebase-auth.js';


let modalEl = document.getElementById("forgetModal");
let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

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
    const forgetForm = document.getElementById('forget-form');
    if (!forgetForm) return;

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
    forgetForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Add a loading state to the button
        const submitButton = forgetForm.querySelector('button[type="submit"]');
        setButtonLoading(submitButton, true);

        const email = document.getElementById('forget_email').value;

        try {

            const response = await fetch(`${app_url}forget`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content")
                },
                body: JSON.stringify({
                    email: email
                })
            });

            if (!response.ok) {
                setButtonLoading(submitButton, false);
                const data = await response.json();
                showAlert("No se logró enviar la solicitud", data.message, "", "danger");
                return;
            }
            const data = await response.json();
            console.log(data);
            setButtonLoading(submitButton, false);
            if(data.success){
                showAlert("Solicitud enviada",  data.message, "", "success");
                document.getElementById("forget-form").reset();
                bsModal.hide();
            }else{
                showAlert("No se logró enviar la solicitud", data.message, "", "danger");

            }
            
        } catch (error) {
            console.log(error);
            setButtonLoading(submitButton, false);
            showAlert("No se logró enviar la solicitud", "Intente de nuevo", "", "danger");
            return error;
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
