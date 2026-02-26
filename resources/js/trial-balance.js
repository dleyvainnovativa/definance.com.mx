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


function buildHeaderCards(data) {
    console.log(data);
    let html = '';
    data.forEach(group => {
        const colClass = 'col-12 col-md-12 col-lg-4 col-xl-3'
        if (group.type === 'calculate') {
            html += `

<div class="${colClass}">
        <div class="card card-dark border border-dark bg-dark">
            <div class="card-body p-4 text-dark">
                <div class="row g-2">
                    <div class="col-auto me-auto">
                        <h6 class="my-0">${group.title}</h6>
                    </div>
                    <div class="col-auto ms-auto">
                        <h6 class="my-0"><i class="fa ${group.icon} text-primary" aria-hidden="true"></i></h6>
                    </div>
                    <div class="col-12">
                        <h3 id="revenue-value" class="${formatTextClass(group.total)} my-0 fw-bold">${formatCurrency(group.total)}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
`;
        }
        document.getElementById('cards-header').innerHTML = html;
    });
    document.getElementById('cards-header').innerHTML = html;
}

function responseHandler(res) {
    setTimeout(() => {
        if (res.headers) {
            buildHeaderCards(res.headers);
        }
    }, 0);
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
    el.innerHTML = items.map(v => `
        <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   value="${v}"
                   data-filter="${key}">
            <label class="form-check-label">${v}</label>
        </div>
    `).join('');
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
        console.log(row);
        let icon = getEntryIcon(row.entry_type);

        view += template
            .replace('%icon%', getEntryIcon(row.entry_type))
.replace('%account_name%', row.account_name)
.replace('%account_code%', row.account_code)
.replace('%entry_type_label%', row.entry_type_label)
.replace('%nature%', row.nature)
.replace('%opening%', row.opening.toFixed(2))
.replace('%debit%', row.debit.toFixed(2))
.replace('%credit%', row.credit.toFixed(2))
.replace('%total%', row.total.toFixed(2))
.replace('%total_class%', row.total >= 0 ? 'text-success' : 'text-danger')


        })

        return `<div class="row g-4">${view}</div>`
    }
