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
    $('#journal-table').bootstrapTable('refresh', { pageNumber: 1 });
};

document.getElementById('filters-apply').onclick = () => {
    selectedDebitAccounts = getChecked('debit');
    selectedCreditAccounts = getChecked('credit');
    selectedTypes = getChecked('entry_type');
    startDate = document.getElementById("filter_start_date").value;
    endDate = document.getElementById("filter_end_date").value;


    $('#journal-table').bootstrapTable('refresh', { pageNumber: 1 });

    bootstrap.Offcanvas.getInstance(
        document.getElementById('offcanvasFilter')
    ).hide();
};

window.customViewFormatter = data => {
    const template = $('#tableTemplate').html()
    let view = ''

    $.each(data, function (i, row) {
        const debit  = parseFloat(row.debit)  || 0
        const credit = parseFloat(row.credit) || 0
        const hasDebit  = debit  > 0
        const hasCredit = credit > 0

        const icon        = getEntryIcon(row.entry_type)
        const iconBg      = hasDebit ? 'bg-danger bg-opacity-10' : 'bg-primary bg-opacity-10'
        const badgeCls    = hasDebit ? 'text-bg-danger'          : 'text-bg-success'
        const amountLabel = hasDebit ? 'Cargo'                   : 'Abono'

        const debitStr  = hasDebit  ? formatCurrency(row.debit)  : '—'
        const creditStr = hasCredit ? formatCurrency(row.credit) : '—'
        const debitCls  = hasDebit  ? 'text-danger'  : 'text-secondary'
        const creditCls = hasCredit ? 'text-primary'  : 'text-secondary'

        view += template
            .replace('%icon_bg%',             iconBg)
            .replace('%icon%',                icon)
            .replace('%entry_type_label%',    row.entry_type_label  ?? '—')
            .replace('%entry_date%',          row.entry_date        ?? '—')
            .replace('%badge_class%',         badgeCls)
            .replace('%amount_label%',        amountLabel)
            .replace('%debit_account_name%',  row.debit_account_name  ?? '—')
            .replace('%debit_account_code%',  row.debit_account_code  ?? '—')
            .replace('%credit_account_name%', row.credit_account_name ?? '—')
            .replace('%credit_account_code%', row.credit_account_code ?? '—')
            .replace('%description%',         row.description         ?? '—')
            .replace('%debit_class%',         debitCls)
            .replace('%debit%',               debitStr)
            .replace('%credit_class%',        creditCls)
            .replace('%credit%',              creditStr)
    })

    return `<div class="row g-4">${view}</div>`
}
