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

    let filters = 
        {
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
            return '<strong>' + number.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }) + '</strong>';
        }
    }
    return '<strong>-</strong>';
}
window.footerSum = footerSum;

document.addEventListener('DOMContentLoaded', function () {
    const $table = $('#journal-table');
    if ($table.length) {
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
    $('#journal-table').bootstrapTable('refresh', { pageNumber: 1 });
};

document.getElementById('filters-apply').onclick = () => {
    selectedDebitAccounts = getChecked('debit');
    selectedCreditAccounts = getChecked('credit');
    selectedTypes = getChecked('entry_type');


    $('#journal-table').bootstrapTable('refresh', { pageNumber: 1 });

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
            .replace('%debit%', row.debit)
            .replace('%credit%', row.credit)
            .replace('%subtitle%', `${accountName} (${accountCode})`)
            .replace('%amount%', amount)
            .replace('%amount_class%', amountClass)

        })

        return `<div class="row g-4">${view}</div>`
    }
