function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    let data_url = document.getElementById("data_url").value;
    let params = null;
        const url = new URL(data_url);

const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;

    if (month) url.searchParams.set('month', month);
    if (year) url.searchParams.set('year', year);

    fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(res => res.json())
        .then(data => {
            // console.log(data);
            initAccountGroups(data.debit_accounts, "debit-accounts");
            initAccountGroups(data.credit_accounts, "credit-accounts");
            buildHeaderCards(data.income_month, "income_month");
            buildHeaderCards(data.income_year, "income_year");
            buildHeaderCards(data.balance, "balance");
            buildTable(data.journal, "journal-table");
            
            // params.success(data);
        })
        .catch((error) => {
            console.log(error);
        });
}
initRequest();

function initAccountGroups(groups, id) {
    const select = document.getElementById(`select-${id}`);
    let options = '';
    groups.forEach((group, index) => {
        options += `
            <option value="${index}">
                ${group.name}
            </option>
        `;
    });
    select.innerHTML = options;
    if (groups.length > 0) {
        buildAccounts(groups[0].accounts, id);
    }
    select.addEventListener('change', function () {
        const selectedIndex = parseInt(this.value);
        buildAccounts(groups[selectedIndex].accounts, id);
    });
}

function buildAccounts(data, id) {
    let html = '';
    let total = 0;
    data.forEach(account => {
        total += parseFloat(account.total);
        html += `
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 col-xxl-3">
            <div class="card card-dark border border-dark bg-dark h-100">
                <div class="card-body p-4 text-dark">
                    <div class="row g-2">

                        <div class="col-auto me-auto">
                            <h6 class="my-0 fw-bold">${account.account_code}</h6>
                            <h6 class="text-muted my-0 fw-light">${account.account_name}</h6>
                        </div>

                        <div class="col-12 mt-2">
                            <h4 class="${formatTextClass(account.total)} fw-bold">
                                ${formatCurrency(account.total)}
                            </h4>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        `;
    });
    console.log(total);
    document.getElementById(`cards-${id}-total`).textContent = `${formatCurrency(total)}`;
    document.getElementById(`cards-${id}`).innerHTML = html;
}

function buildHeaderCards(data, id) {
    let html = '';
    data.forEach(group => {
        let colClass = 'col-12 col-md-6 col-lg-6 col-xl-4 col-xxl-4';
        let total = ``;
        if (group.display === 'operation' ) {
            // colClass = 'col-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6';
        }else if(group.display === 'total'){
            colClass = 'col-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4';
            // total = `(Ingresos - Gastos)`;
        }
            html += `

<div class="${colClass}">
        <div class="card card-dark border border-dark bg-dark h-100">
            <div class="card-body p-4 text-dark">
                <div class="row g-2">
                    <div class="col-auto me-auto">
                        <h6 class="my-0 fw-bold">${group.title}</h6>
                    </div>
                    <div class="col-auto ms-auto">
                        <h6 class="my-0"><i class="fa ${group.icon} text-primary" aria-hidden="true"></i></h6>
                    </div>
                    <div class="col-12">
                        <h6 class="text-muted my-0 fw-light">${group.description}</h6>
                    </div>
                    <div class="col-12">
                        <h3 id="revenue-value" class="${formatTextClass(group.total)} my-0 fw-bold">${formatCurrency(group.total)}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
`;

        document.getElementById(`cards-header-${id}`).innerHTML = html;
    });
    document.getElementById(`cards-header-${id}`).innerHTML = html;
}

function buildTable(data, id) {
    const table = $("#" + id);

    // destroy previous instance if it exists
    table.bootstrapTable('destroy');

    // rebuild table with existing options
    table.bootstrapTable({
        ...tableOptions,
        data: data
    });

    if (isMobile()) {
            table.bootstrapTable('toggleCustomView', true);
        }
}

document.getElementById("refresh").addEventListener("click", initRequest);
document.getElementById("hidden_numbers").addEventListener("click", toggleSensitiveData);



function toggleSensitiveData() {

    const elements = document
        .getElementById("dashboard-container")
        // .querySelectorAll(".text-success,.text-muted");
    elements.classList.toggle("blur-sensitive");
    // elements.forEach(element => {
    //     element.classList.toggle("blur-sensitive");
    // });
}


window.customViewFormatter = data => {
    const template = $('#tableTemplate').html()
    let view = ''

    $.each(data, function (i, row) {
        const debit  = parseFloat(row.debit)  || 0
        const credit = parseFloat(row.credit) || 0
        const hasDebit  = debit  > 0
        const hasCredit = credit > 0

        // Icon + badge styling based on entry type
        const icon     = getEntryIcon(row.entry_type)
        const iconBg   = hasDebit ? 'bg-danger bg-opacity-10'  : 'bg-primary bg-opacity-10'
        const badgeCls = hasDebit ? 'text-bg-danger'           : 'text-bg-success'
        const amountLabel = hasDebit ? 'Cargo' : 'Abono'

        // Amount columns
        const debitStr  = hasDebit  ? `${formatCurrency(debit)}`  : '—'
        const creditStr = hasCredit ? `${formatCurrency(credit)}` : '—'
        const debitCls  = hasDebit  ? 'text-danger'  : 'text-secondary'
        const creditCls = hasCredit ? 'text-primary'  : 'text-secondary'

        view += template
            .replace('%icon_bg%',             iconBg)
            .replace('%icon%',                icon)
            .replace('%entry_type_label%',    row.entry_type_label ?? '—')
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