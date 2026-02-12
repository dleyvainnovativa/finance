(() => {
    'use strict'
    const forms = document.querySelectorAll('#account-form.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', async event => {
            const submitButton = form.querySelector('button[type="submit"]');

            setButtonLoading(submitButton, true);
            event.preventDefault(); // always prevent native submit

            if (!form.checkValidity()) {
                event.stopPropagation();
                await setButtonLoading(submitButton, false);
            } else {
                addAccount(form);
                await setButtonLoading(submitButton, false);
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

let accountsGrid = null;


function initRequest() {
    const url = `${api_url}accounts`;
    const token = localStorage.getItem('finance_auth_token');
    let accounts_list = [];
    if (!accountsGrid) {
        accountsGrid = new Grid({
            columns: ['Acciones', 'Código', 'Nombre', 'Tipo'],
            search: true,
            sort: true,
            pagination: { limit: 10 },
            server: {
                url: url,
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                then: data => {
                    const rows = data.data.map(account => [
                        html(`
                            <div class="row g-1">
                                <div class="col-auto">
                                    <button class="btn btn-primary btn-sm" onclick="editAccount('${account.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-danger btn-sm" onclick="deleteAccount('${account.id}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `),
                        account.code,
                        account.name,
                        html(`<span class="badge text-bg-primary">${account.type}</span>`),
                    ]);

                    window.accounts_list = data.data;
                    buildSelect(data);
                    return rows;
                },
                total: data => data.total
            }
        }).render(document.getElementById('accountsGrid'));
    } else {
        // 🔥 this triggers a re-fetch + re-render
        accountsGrid.updateConfig({}).forceRender();
    }
}

window.accountChoices = null;

async function buildSelect(accounts) {
    const select = document.querySelector("#parent_id");

    if (!window.accountChoices) {
        // initialize once
        window.accountChoices = new Choices(select, {
            searchPlaceholderValue: "Buscar cuenta...",
            removeItemButton: false,
            shouldSort: false,
        });
    } else {
        // if already initialized, clear previous choices
        window.accountChoices.clearChoices();
    }

    // set new choices
    window.accountChoices.setChoices(
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

const parentSelect = document.getElementById("parent_id");

parentSelect.addEventListener("change", function () {
    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
    if (!selectedOption) return;

    const rawProps = selectedOption.getAttribute("data-custom-properties");
    if (!rawProps) return;

    const {
        type,
        code: parentCode,
        parent_id
    } = JSON.parse(rawProps);

    document.getElementById("badge_root").textContent = type;
    document.getElementById("code_prefix").textContent = `${parentCode}.`;
    document.getElementById("account_type").value = `${type}`;
    document.getElementById("account_parent_id").value = parent_id;

    // 🔍 Find children of this parent
    const children = accounts_list.filter(acc =>
        acc.code?.startsWith(parentCode + ".")
    );
    console.log(children);

    //  Get next consecutive
    let nextNumber = 1;

    if (children.length > 0) {
        
        const lastNumbers = children.map(acc => {
            const parts = acc.code.split(".");
            console.log(parts);
            return parseInt(parts[parts.length -1], 10);
        });
        console.log(lastNumbers);
        nextNumber = Math.max(...lastNumbers) + 1;
        console.log(nextNumber);
    }

    // ✍️ Set value
    document.getElementById("code").value = nextNumber;
    document.getElementById("account_code").value = `${parentCode}.${nextNumber}`;

});

async function addAccount(form) {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
        code: form.account_code.value,
        name: form.account_name.value,
        type: form.account_type.value,
        parent_id: form.account_parent_id.value
    };

    try {
        const response = await fetch(`${api_url}accounts`, {
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

        bootstrap.Modal.getInstance(
            document.getElementById('accountModal')
        ).hide();
        form.reset();
        document.getElementById("badge_root").textContent = "";
    document.getElementById("code_prefix").textContent = "0";
        initRequest();


        const data = await response.json();
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

        return data;

    } catch (error) {
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }
}

initRequest();