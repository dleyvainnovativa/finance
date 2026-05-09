import {
    changePassword
} from './firebase/firebase-auth.js';

function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    let data_url = document.getElementById("data_url").value;
    const url = new URL(data_url);
    fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(res => res.json())
        .then(data => {
            console.log(data);
            document.getElementById('profile_name').value = data.user.name;
            document.getElementById('profile_email').value = data.user.email;
        })
        .catch((error) => {
            console.log(error);
        });
}

initRequest();

const forms = document.querySelectorAll('#change_profile_form.needs-validation')
Array.from(forms).forEach(form => {
    form.addEventListener('submit', async event => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (typeof setButtonLoading === 'function') setButtonLoading(submitButton, true);
        event.preventDefault();
        if (!form.checkValidity()) {
            event.stopPropagation();
            if (typeof setButtonLoading === 'function') await setButtonLoading(submitButton, false);
        } else {
            let response = await updateProfile(form);
            if (response) {
                location.reload();
            }
            if (typeof setButtonLoading === 'function') await setButtonLoading(submitButton, false);
        }
        form.classList.add('was-validated');
    }, false);
});

const formsPassword = document.querySelectorAll('#change_password_form.needs-validation')
Array.from(formsPassword).forEach(form => {
    form.addEventListener('submit', async event => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (typeof setButtonLoading === 'function') setButtonLoading(submitButton, true);
        event.preventDefault();
        if (!form.checkValidity()) {
            event.stopPropagation();
            if (typeof setButtonLoading === 'function') await setButtonLoading(submitButton, false);
        } else {
            if (
                form.new_password.value !==
                form.new_password_confirmation.value
            ) {
                showAlert("Error al actualizar", "Las contraseñas no coinciden", "", "danger")

                if (typeof setButtonLoading === 'function') {
                    await setButtonLoading(submitButton, false);
                }
                return;
            }
            if (form.new_password.value.length < 6) {
                showAlert("Error al actualizar", "La contraseña debe tener al menos 6 caracteres", "", "danger")

                if (typeof setButtonLoading === 'function') {
                    await setButtonLoading(submitButton, false);
                }
                return;
            }
            try {
                const result = await changePassword(
                    form.password.value,
                    form.new_password.value
                );
                if (result.success) {
                    showAlert("Contraseña actualizada", "Se ha actualizado correctamente la contraseña", "", "success");
                    form.reset();
                    form.classList.remove('was-validated');
                } else {
                    showAlert("Error al actualizar", result.message, "", "danger");
                }
            } catch (error) {
                showAlert("Error al actualizar", "Ocurrió un error al cambiar la contraseña", "", "danger");
            } finally {
                if (typeof setButtonLoading === 'function') {
                    await setButtonLoading(submitButton, false);
                }
            }
        }
        form.classList.add('was-validated');
    }, false);
});


async function updateProfile(form) {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;
    const payload = {
        "name": form.name.value,
    };

    try {
        const response = await fetch(`${api_url}profile`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (!response.ok) {
            if (typeof handleApiError === 'function') handleApiError(response.status, data);
            return null;
        }
        if (typeof showAlert === 'function') showAlert("Datos actualizados", "Se han registrado correctamente los datos", "", "success");
        return data;
    } catch (error) {
        console.error(error);
        if (typeof showAlert === 'function') showAlert("Ha ocurrido un error", "No se han registrado correctamente los datos, intente de nuevo", "", "danger");
        return null;
    }
}


document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function () {
        const target = document.getElementById(
            this.dataset.target
        );
        const icon = this.querySelector('i');
        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            target.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
