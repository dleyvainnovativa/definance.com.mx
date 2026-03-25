let allAccounts = [];
let debitChoices, creditChoices;
const debitSelect = document.getElementById('journal_debit_account_id');
const creditSelect = document.getElementById('journal_credit_account_id');
(() => {
    'use strict'


    const typeSelect = document.getElementById('journal_entry_type');
    debitChoices = new Choices(debitSelect, {
        searchPlaceholderValue: "Buscar cuenta...",
        removeItemButton: false,
        shouldSort: false
    });
    creditChoices = new Choices(creditSelect, {
        searchPlaceholderValue: "Buscar cuenta...",
        removeItemButton: false,
        shouldSort: false
    });

    // debitChoices.disable();
    // creditChoices.disable();

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

    // 2. Fetch data ONCE and store it
    function initRequest() {
        const token = localStorage.getItem('finance_auth_token');
        if (!token) return;

        fetch(`${api_url}accounts/entries`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(response => response.json())
            .then(data => {
                // Store the data globally so we can filter it later
                allAccounts = data.data;
                populateChoices(debitChoices, allAccounts);
                populateChoices(creditChoices, allAccounts);
            })
            .catch(error => console.error(error));
    }

    // 3. Handle Workflow Changes (entry_type select)
    typeSelect.addEventListener('change', (e) => {
        const type = e.target.value;

        // Reset both selects when type changes
        debitChoices.clearStore();
        creditChoices.clearStore();
        creditChoices.disable(); // Always start credit as disabled on type change

        if (['income', 'expense', 'transfer', 'asset_acquisition'].includes(type)) {
            // NORMAL WORKFLOW
            populateChoices(debitChoices, allAccounts);
            populateChoices(creditChoices, allAccounts);
            debitChoices.enable();

        } else if (type === 'opening_balance') {
            // OPENING BALANCE WORKFLOW (Nature == debit)
            const filteredAccounts = allAccounts.filter(acc => acc.nature === 'debit');
            populateChoices(debitChoices, filteredAccounts);
            debitChoices.enable();

        } else if (type === 'opening_balance_credit') {
            // OPENING BALANCE CREDIT WORKFLOW (Nature == credit)
            const filteredAccounts = allAccounts.filter(acc => acc.nature === 'credit');
            populateChoices(debitChoices, filteredAccounts);
            debitChoices.enable();
        }
    });

    // 4. Handle cross-disabling for normal workflow
    debitSelect.addEventListener('change', (e) => {
        const currentType = typeSelect.value;

        // Only apply this cross-disabling logic to the normal workflows
        if (['income', 'expense', 'transfer', 'asset_acquisition'].includes(currentType)) {
            const selectedDebitId = e.detail.value; // Get selected value from Choices.js event

            populateChoices(creditChoices, allAccounts, selectedDebitId);
            creditChoices.enable();
        }
    });



    initRequest();
})();

// 5. Helper function to feed data into Choices instances
async function populateChoices(choicesInstance, accountsList, disabledId = null) {
    const formattedChoices = accountsList.map(m => ({
        value: m.id,
        label: `${m.code} - ${m.name}`,
        disabled: String(m.id) === String(disabledId), // Disable if it matches the other select
        customProperties: {
            code: m.code,
            type: m.type,
            parent_id: m.id,
            root: m.name,
            nature: m.nature
        }
    }));

    // The 'true' parameter tells Choices.js to replace existing choices entirely
    choicesInstance.setChoices(formattedChoices, 'value', 'label', true);
}

async function editJournal(form) {
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = {
        debit_account_id: form.journal_debit_account_id.value,
        credit_account_id: form.journal_credit_account_id.value,
        entry_id: form.journal_entry_id.value,
        entry_date: form.journal_entry_date.value,
        entry_type: form.journal_entry_type.value,
        amount: form.journal_entry_amount.value,
        description: form.journal_entry_concept.value,
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
        // Reset Choices internal state
        debitChoices.clearStore();
        creditChoices.clearStore();

        // Re-populate choices
        populateChoices(debitChoices, allAccounts);
        populateChoices(creditChoices, allAccounts);
        showAlert("Perfil actualizado", "Se han actualizado correctamente los datos", "", "success")

    } catch (error) {
        console.log(error);
        showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

        return error;
    }

}
async function removeJournal() {
    bsModal.hide();
    let modal = await confirmModal({
        title: "¿Estás seguro de borrar este registro?",
        text: 'Estás a punto de borrar este registro para siempre',
        mode: 'warning',
        confirmText: 'Borrar registro'
    });
    if (modal) {
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

window.removeJournal = removeJournal;

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
        "start_date": (startDate),
        "end_date": (endDate),
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

document.addEventListener('DOMContentLoaded', async function () {
    const $table = $('#journal-table');
    if ($table.length) {
        tableOptions.onClickRow = async function (row, $event, field) {
            console.log(row);
            if (row.id) {
                let debit = parseFloat(row.debit) || 0;
                let credit = parseFloat(row.credit) || 0;

                let amount = 0;

                if (debit === 0) {
                    amount = credit;
                } else if (credit === 0) {
                    amount = debit;
                }


                await populateChoices(debitChoices, allAccounts, row.credit_account_id);
                await populateChoices(creditChoices, allAccounts, row.debit_account_id);
                // document.getElementById("journal_debit_account_id").value = row.dedit_account_id;
                // document.getElementById("journal_credit_account_id").value = row.credit_account_id;
                debitChoices.setChoiceByValue(row.debit_account_id);
                creditChoices.setChoiceByValue(row.credit_account_id);
                document.getElementById("journal_entry_id").value = row.id;
                document.getElementById("journal_entry_date").value = formatDate(row.entry_date);
                document.getElementById("journal_entry_type").value = row.entry_type;
                document.getElementById("journal_entry_amount").value = amount;
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
let startDate = null;
let endDate = null;

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
    startDate = null;
    endDate = null;
    $('#journal-table').bootstrapTable('refresh', {
        pageNumber: 1
    });
};

document.getElementById('filters-apply').onclick = () => {
    selectedDebitAccounts = getChecked('debit');
    selectedCreditAccounts = getChecked('credit');
    selectedTypes = getChecked('entry_type');
    startDate = document.getElementById("filter_start_date").value;
    endDate = document.getElementById("filter_end_date").value;

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

        let icon = getEntryIcon(row.entry_type);

        view += template
            .replace('%icon%', icon)
            .replace('%title%', row.description)
            .replace('%subtitle%', `${accountName} (${accountCode})`)
            .replace('%amount%', amount)
            .replace('%amount_class%', amountClass)

    })

    return `<div class="row g-4">${view}</div>`
}


async function removeMultipleJournals() {
    const selected = $('#journal-table').bootstrapTable('getSelections');
    console.log(selected);

    if (!selected.length) {
        showAlert("Selecciona registros", "Debes seleccionar al menos un registro", "", "warning");
        return;
    }

    const confirm = await confirmModal({
        title: `¿Eliminar ${selected.length} registros?`,
        text: 'Esta acción no se puede deshacer',
        mode: 'warning',
        confirmText: 'Eliminar'
    });

    if (!confirm) return;

    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;

    const payload = selected.map(row => ({
        entry_id: `${row.id}`,
        entry_date: row.entry_date
    }));

    console.log(payload);

    try {
        const response = await fetch(`${api_url}entries/bulk-delete`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                entries: payload
            })
        });

        if (!response.ok) {
            handleApiError(response.status);
            return;
        }

        $('#journal-table').bootstrapTable('refresh');

        showAlert(
            "Registros eliminados",
            "Los registros seleccionados fueron eliminados correctamente",
            "",
            "success"
        );

    } catch (error) {
        console.log(error);
        showAlert("Error", "No se pudieron eliminar los registros", "", "danger");
    }
}

window.removeMultipleJournals = removeMultipleJournals;
