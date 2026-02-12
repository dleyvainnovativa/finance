import { loginWithEmail, loginWithGoogle } from '/resources/js/firebase/firebase-auth.js';

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    const email = emailInput.value;
    const password = passwordInput.value;

    try {
        await loginWithEmail(email, password);
    } catch (err) {
        const errorBox = document.getElementById('error-message');
        errorBox.innerText = err.message;
        errorBox.style.display = 'block';
    }
});

document.querySelector('.btn-outline-primary').addEventListener('click', async (e) => {
    e.preventDefault();
    await loginWithGoogle();
});
