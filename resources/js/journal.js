(() => {
    'use strict'
    const forms = document.querySelectorAll('#journal-form.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', async event => {
            const submitButton = form.querySelector('button[type="submit"]');

            setButtonLoading(submitButton, true);
            event.preventDefault(); // always prevent native submit

            if (!form.checkValidity()) {
                event.stopPropagation();
                await setButtonLoading(submitButton, false);

            } else {

                await editJournal(form);
                await setButtonLoading(submitButton, false);

            }
            form.classList.add('was-validated');
        }, false);
    });
})();

async function editJournal(form){
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
        entry_id: form.journal_entry_id.value,
        entry_date: form.journal_entry_date.value,
        entry_type: form.journal_entry_type.value,
        amount: form.journal_entry_amount.value,
        description: form.journal_entry_concept.value,
        reference: form.journal_entry_reference.value
    };

    try {
        const response = await fetch(`${api_url}entries`, {
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
        bsModal.hide();
        form.reset();
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

    } catch (error) {
        console.log(error);
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }

}
async function removeJournal(){
    bsModal.hide();
    let modal = await confirmModal({
            title: "¿Estás seguro de borrar este registro?",
            text: 'Estás a punto de borrar este registro para siempre',
            mode: 'warning',
            confirmText: 'Borrar registro'
        });
        if(modal){
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
        entry_id: document.getElementById("journal_entry_id").value,
        entry_date: document.getElementById("journal_entry_date").value,
    };

    try {
        const response = await fetch(`${api_url}entries`, {
            method: 'DELETE',
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
        bsModal.hide();
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

    } catch (error) {
        console.log(error);
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }
    }

}

window.removeJournal=removeJournal;

function ajaxRequest(params) {
    const token = localStorage.getItem('finance_auth_token');
    const url = new URL(params.url);

    url.searchParams.set('search', params.data.search || '');
    url.searchParams.set('page', (params.data.offset / params.data.limit) + 1);
    url.searchParams.set('limit', params.data.limit);

    const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;
    if (month) url.searchParams.set('month', month);
    if (year) url.searchParams.set('year', year);

    let filters = {
        "debit_accounts": (selectedDebitAccounts),
        "credit_accounts": (selectedCreditAccounts),
        "types": (selectedTypes),
    };

    url.searchParams.set(
        'filters',
        JSON.stringify(filters)
    );

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
window.ajaxRequest = ajaxRequest;

function responseHandler(res) {
    return {
        total: res.total,
        rows: res.data,
        footer: res.footer
    };
}
window.responseHandler = responseHandler;

function footerLabel() {
    return '<strong>Totales</strong>';
}
window.footerLabel = footerLabel;

function footerNullText() {
    return '';
}
window.footerNullText = footerNullText;

function footerSum() {
    const field = this.field;
    const footerData = $('#journal-table').bootstrapTable('getFooterData');
    if (footerData && footerData.length > 0) {
        const value = footerData[0][field];
        if (value !== null && value !== undefined) {
            const number = parseFloat(value);
            return '<strong>' + number.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }) + '</strong>';
        }
    }
    return '<strong>-</strong>';
}
window.footerSum = footerSum;

let modalEl = document.getElementById("journalModal");
        let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

document.addEventListener('DOMContentLoaded', function () {
    const $table = $('#journal-table');
    if ($table.length) {
        tableOptions.onClickRow = function (row, $event, field) {
            console.log(row);
            if(row.id){
                let debit = parseFloat(row.debit) || 0;
                let credit = parseFloat(row.credit) || 0;

                let amount = 0;

                if (debit === 0) {
                    amount = credit;
                } else if (credit === 0) {
                    amount = debit;
                }
                document.getElementById("journal_entry_id").value = row.id;
                document.getElementById("journal_entry_date").value = formatDate(row.entry_date);
                document.getElementById("journal_entry_type").value = row.entry_type_label;
                document.getElementById("journal_entry_amount").value = amount;
                document.getElementById("journal_entry_reference").value = row.reference;
                document.getElementById("journal_entry_concept").value = row.description;
                bsModal.show();
            }
        };
        $table.bootstrapTable(tableOptions);
        if (isMobile()) {
            $table.bootstrapTable('toggleCustomView', true);
        }
    }
    $('#month-filter, #year-filter').on('change', function () {
        $table.bootstrapTable('refresh', {
            pageNumber: 1
        });
    });
});

// FILTER
let selectedDebitAccounts = [];
let selectedCreditAccounts = [];
let selectedTypes = ['income', 'expense', 'transfer', 'asset_acquisition'];

function loadAccountFilters() {
    fetch(`${api_url}journal/filters`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('finance_auth_token')}`
            }
        })
        .then(res => res.json())
        .then(data => {
            renderCheckboxList('#filter-debit-accounts', data.debit_accounts, 'debit');
            renderCheckboxList('#filter-credit-accounts', data.credit_accounts, 'credit');
        });
}
function renderCheckboxList(container, items, key) {
    const el = document.querySelector(container);

    el.innerHTML = items.map((v, i) => {
        const id = `${key}_${i}`;

        return `
        <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   id="${id}"
                   value="${v}"
                   data-filter="${key}">
            <label class="form-check-label" for="${id}">
                ${v}
            </label>
        </div>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', loadAccountFilters);

function getChecked(key) {
    return [...document.querySelectorAll(`input[data-filter="${key}"]:checked`)]
        .map(i => i.value);
}

function setAll(key, checked) {
    document.querySelectorAll(`input[data-filter="${key}"]`)
        .forEach(i => i.checked = checked);
}

document.getElementById('debit-clear').onclick = () => setAll('debit', false);
document.getElementById('credit-clear').onclick = () => setAll('credit', false);

document.getElementById('debit-select-all').onclick = () => setAll('debit', true);
document.getElementById('credit-select-all').onclick = () => setAll('credit', true);

document.getElementById('filters-reset').onclick = () => {
    setAll('debit', false);
    setAll('credit', false);
    selectedDebitAccounts = [];
    selectedCreditAccounts = [];
    $('#journal-table').bootstrapTable('refresh', {
        pageNumber: 1
    });
};

document.getElementById('filters-apply').onclick = () => {
    selectedDebitAccounts = getChecked('debit');
    selectedCreditAccounts = getChecked('credit');
    selectedTypes = getChecked('entry_type');

    $('#journal-table').bootstrapTable('refresh', {
        pageNumber: 1
    });

    bootstrap.Offcanvas.getInstance(
        document.getElementById('offcanvasFilter')
    ).hide();
};

window.customViewFormatter = data => {
    const template = $('#tableTemplate').html()
    let view = ''

    $.each(data, function (i, row) {
        // Resolve account (debit or credit)
        const accountName = row.debit_account_name ?? row.credit_account_name ?? '—'
        const accountCode = row.debit_account_code ?? row.credit_account_code ?? '—'

        // Determine amount + color
        let amount = '0.00'
        let amountClass = 'text-muted'

        if (parseFloat(row.debit) > 0) {
            amount = parseFloat(row.debit).toFixed(2)
            amountClass = 'text-success'
        } else if (parseFloat(row.credit) > 0) {
            amount = parseFloat(row.credit).toFixed(2)
            amountClass = 'text-danger'
        }

        let icon = getEntryIcon(row.entry_type_label);

        view += template
            .replace('%icon%', icon)
            .replace('%title%', row.description)
            .replace('%subtitle%', `${accountName} (${accountCode})`)
            .replace('%amount%', amount)
            .replace('%amount_class%', amountClass)

    })

    return `<div class="row g-4">${view}</div>`
}
