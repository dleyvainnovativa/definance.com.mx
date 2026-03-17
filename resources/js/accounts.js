let editModalEl = document.getElementById("accountEditModal");
let editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);

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

                await addAccount(form);
                await setButtonLoading(submitButton, false);

            }
            form.classList.add('was-validated');
        }, false);
    });
})();
(() => {
    'use strict'
    const forms = document.querySelectorAll('#account-edit-form.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', async event => {
            const submitButton = form.querySelector('button[type="submit"]');

            setButtonLoading(submitButton, true);
            event.preventDefault(); // always prevent native submit

            if (!form.checkValidity()) {
                event.stopPropagation();
                await setButtonLoading(submitButton, false);

            } else {

                await editAccountForm(form);
                await setButtonLoading(submitButton, false);

            }
            form.classList.add('was-validated');
        }, false);
    });
})();

function ajaxRequest(params) {
    const token = localStorage.getItem('finance_auth_token');
    const url = new URL(params.url);
    url.searchParams.set('search', params.data.search || '');
    url.searchParams.set('page', (params.data.offset / params.data.limit) + 1);
    url.searchParams.set('limit', params.data.limit);
    fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(res => res.json())
        .then(data => {
            params.success(data);
        })
        .catch(() => params.error());
}

initRequest()

function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) {
        return;
    }
    fetch(`${api_url}accounts/all?type=create`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            let accounts_list = [];

            window.accounts_list = data.data;

            buildSelect(data);

        })
        .catch(error => {
            console.error(error);
        });
}

window.ajaxRequest = ajaxRequest;


function actionsFormatter(value, row) {
    let remove = `
    <button disabled 
        class="btn btn-outline-danger"
        title="Eliminar cuenta">
        <i class="fa-solid fa-trash"></i>
    </button>
    `;
    let edit = `
    <button disabled 
    class="btn btn-outline-primary"
    title="Editar cuenta">
    <i class="fa-solid fa-pen"></i>
</button>
    `;
    if (row.is_editable) {
        edit = `
<button
    class="btn btn-outline-primary"
    onclick="editAccount(${value}, '${row.name}')"
    title="Editar cuenta">
    <i class="fa-solid fa-pen"></i>
</button>
`;
    }
    if (row.is_deletable) {
        remove = `
     <button
        class="btn btn-outline-danger"
        onclick="removeAccount(${value},'${row.name}')"
        title="Eliminar cuenta">
        <i class="fa-solid fa-trash"></i>
    </button>
     `;
    }
    return `<div class="btn-group btn-group-sm">
                        ${edit}
                        ${remove}

                        
                    </div>`;
}
window.actionsFormatter = actionsFormatter


function responseHandler(res) {
    return {
        total: res.total,
        rows: res.data,
    };
}
window.responseHandler = responseHandler;

document.addEventListener('DOMContentLoaded', function () {
    const $table = $('#journal-table');
    if ($table.length) {
        $table.bootstrapTable(tableOptions);
        if (isMobile()) {
            $table.bootstrapTable('toggleCustomView', true);
        }
    }
});

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
            disabled: !m.allows_children,
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

    const children = accounts_list.filter(acc => {
        if (!acc.code) return false;

        const parts = acc.code.split(".");
        const parentParts = parentCode.split(".");

        // Must start with parent
        if (!acc.code.startsWith(parentCode + ".")) return false;

        // Must be exactly one level deeper
        return parts.length === parentParts.length + 1;
    });
    console.log(children);

    //  Get next consecutive
    let nextNumber = 1;

    if (children.length > 0) {

        const lastNumbers = children.map(acc => {
            const parts = acc.code.split(".");
            console.log(parts);
            return parseInt(parts[parts.length - 1], 10);
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
        $('#journal-table').bootstrapTable('refresh');
        initRequest();
        bootstrap.Modal.getInstance(
            document.getElementById('accountModal')
        ).hide();
        form.reset();
        document.getElementById("badge_root").textContent = "";
        document.getElementById("code_prefix").textContent = "0";
        const data = await response.json();
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

        return data;

    } catch (error) {
        console.log(error);
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }
}
async function editAccountForm(form) {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
        id: form.account_id.value,
        name: form.account_name.value,
    };

    try {
        const response = await fetch(`${api_url}accounts`, {
            method: 'PUT',
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
        $('#journal-table').bootstrapTable('refresh');
        initRequest();
        editModal.hide();
        form.reset();
        document.getElementById("badge_root").textContent = "";
        document.getElementById("code_prefix").textContent = "0";
        const data = await response.json();
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

        return data;

    } catch (error) {
        console.log(error);
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }
}

window.customViewFormatter = data => {
    const template = $('#tableTemplate').html()
    let view = '';
    $.each(data, function (i, row) {
        const accountName = row.debit_account_name ?? row.credit_account_name ?? '—';
        const accountCode = row.debit_account_code ?? row.credit_account_code ?? '—';
        let amount = '0.00';
        let amountClass = 'text-muted';
        if (parseFloat(row.debit) > 0) {
            amount = parseFloat(row.debit).toFixed(2)
            amountClass = 'text-success'
        } else if (parseFloat(row.credit) > 0) {
            amount = parseFloat(row.credit).toFixed(2)
            amountClass = 'text-danger'
        }
        let icon = getEntryIcon(row.entry_type);
        let edit = ``;
        let remove = ``;

        edit = `onclick="editAccount(${row.id}, '${row.name}')"`;
        remove = `onclick="removeAccount(${row.id},'${row.name}')"`;
        view += template
            .replace('%id%', row.id)
            .replace('%icon%', getEntryIcon(row.type))
            .replace('%account_name%', row.name)
            .replace('%edit%', edit)
            .replace('%remove%', remove)
            .replace('%is_editable%', row.is_editable ? "" : "disabled")
            .replace('%is_deletable%', row.is_deletable ? "" : "disabled")
            .replace('%account_code%', row.code)
            .replace('%type_label%', row.type_label)
            .replace('%nature_label%', row.nature_label)
    });
    return `<div class="row g-4">${view}</div>`;
}

function editAccount(id, name) {
    console.log(id);
    console.log(name);
    document.getElementById("account_edit_id").value = id;
    document.getElementById("account_edit_name").value = name;
    editModal.show();
}
async function removeAccount(id, name) {
    let modal = await confirmModal({
        title: `¿Estás seguro de borrar esta cuenta (${name})?`,
        text: 'Estás a punto de borrar esta cuenta para siempre',
        mode: 'warning',
        confirmText: 'Borrar cuenta'
    });
    if (modal) {
        const token = localStorage.getItem('finance_auth_token');
        if (!token) return;

        const payload = {
            id: `${id}`,
        };

        try {
    const response = await fetch(`${api_url}accounts`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (response.ok) {
        if (data.success) {
            initRequest();
            $('#journal-table').bootstrapTable('refresh');
            showAlert("Cuenta borrada", "Se ha borrado la cuenta", "", "success");
        } else {
            showAlert("Ha ocurrido un error", data.message, "", "danger");
        }
    } else {
        showAlert("Ha ocurrido un error", data.message ?? "Error en la petición", "", "danger");
    }

} catch (error) {
    console.log(error);
    showAlert("Ha ocurrido un error", "No se ha borrado la cuenta, intente de nuevo", "", "danger");
    return error;
}
    }

}
window.editAccount = editAccount;
window.removeAccount = removeAccount;
