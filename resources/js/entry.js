// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
    'use strict'
    const forms = document.querySelectorAll('#entry-form.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', async event => {
        const submitButton = form.querySelector('button[type="submit"]');

            setButtonLoading(submitButton, true);
            event.preventDefault(); // always prevent native submit

            if (!form.checkValidity()) {
                event.stopPropagation();
                await setButtonLoading(submitButton, false);

            } else {

                await addEntry(form);
                await setButtonLoading(submitButton, false);

            }
            form.classList.add('was-validated');
        }, false);
    });
})();


function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) {
        return;
    }
    fetch(`${api_url}accounts`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            buildSelect(data, "debit_account_id");
            buildSelect(data, "credit_account_id");

        })
        .catch(error => {
            console.error(error);
        });
}


async function addEntry(form) {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
"entry_date": form.entry_date.value,
"entry_type": form.entry_type.value,
"amount": form.amount.value,
"debit_account_id": form.debit_account_id.value,
"credit_account_id": form.credit_account_id.value,
"description": form.description.value,
"reference": form.reference.value,
// "applies_se": form.applies_se.value,
// "applies_fe": form.applies_fe.value
    };

    try {
        const response = await fetch(`${api_url}entries`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            handleApiError(response.status, data);
            return;
        }
        const data = await response.json();
        showAlert("Entrada actualizada","Se han actualizado correctamente los datos","","success")
        return data;

    } catch (error) {
        showAlert("Ha ocurrido un error","No se han actualizado correctamente los datos, intente de nuevo","","danger")

        return error;
    }
}


async function buildSelect(accounts, id) {
    const select = document.getElementById(`${id}`);
    let selectChoices = null;
    if (!selectChoices) {
        // initialize once
        selectChoices = new Choices(select, {
            searchPlaceholderValue: "Buscar cuenta...",
            removeItemButton: false,
            shouldSort: false,
        });
    } else {
        // if already initialized, clear previous choices
        selectChoices.clearChoices();
    }

    // set new choices
    selectChoices.setChoices(
        accounts.data.map(m => ({
            value: m.id,
            label: `${m.code} - ${m.name}`,
            selected: false,
            customProperties: {
                code: m.code,
                type: m.type,
                parent_id: m.id,
                root: m.name
            }
        })),
        'value',
        'label',
        false
    );

    console.log("Choices built:", accounts);
}


initRequest();
