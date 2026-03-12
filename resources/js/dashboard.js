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
            buildAccounts(data.accounts, "accounts");
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

function buildAccounts(data, id) {
    let html = '';

    data.forEach(account => {

        html += `
        <div class="col-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
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
}

document.getElementById("refresh").addEventListener("click", initRequest);
