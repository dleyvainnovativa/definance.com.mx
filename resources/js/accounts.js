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
    return `<div class="btn-group btn-group-sm">
                        <button
                            class="btn btn-outline-primary"
                            onclick="editAccount(${value})"
                            title="Editar cuenta">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <button
                            class="btn btn-outline-danger"
                            onclick="removeAccount(${value})"
                            title="Eliminar cuenta">
                            <i class="fa-solid fa-trash"></i>
                        </button>
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
        let edit = `onclick="editAccount(${row.id})"`;
        let remove = `onclick="removeAccount(${row.id})"`;
        view += template
            .replace('%id%', row.id)
            .replace('%icon%', getEntryIcon(row.type))
            .replace('%account_name%', row.name)
            .replace('%edit%', edit)
            .replace('%remove%', remove)
            .replace('%account_code%', row.code)
            .replace('%type_label%', row.type_label)
            .replace('%nature_label%', row.nature_label)
    });
    return `<div class="row g-4">${view}</div>`;
}
