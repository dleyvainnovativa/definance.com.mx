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
            buildHeaderCards(data.data);
            buildCards(data.data, year);
            // params.success(data);
        })
        .catch((error) => {
            console.log(error);
        });
}
initRequest();

function buildHeaderCards(data) {
    let html = '';
    data.forEach(group => {
        let colClass = 'col-12 col-md-6 col-lg-6 col-xl-4 col-xxl-4';
        let total = ``;
        if (group.display === 'operation' ) {
            // colClass = 'col-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6';
        }else if(group.display === 'total'){
            colClass = 'col-12 col-md-12 col-lg-12 col-xl-4 col-xxl-4';
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

        document.getElementById('cards-header').innerHTML = html;
    });
    document.getElementById('cards-header').innerHTML = html;
}

function buildCards(data, year) {
    let html = '';
    data.forEach(group => {
        const colClass = group.display === 'operation' ?
            'col-12 col-md-12 col-lg-12 col-xl-12' :
            'col-12';
        if (group.display === 'operation') {
            html += `
                <div class="${colClass} ">
                    <div class="">
                        <div class="">
                        
                            
                        <div class="mb-3 fs-5 fw-bold"><i class="me-3 text-primary fs-4 fa-solid ${group.icon}" aria-hidden="true">
                        </i>${group.title}</div>

                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <div class="card card-dark border border-dark">
                                    <div class="card-body p-4">
                                        <div class="row g-2">
                                            <div class="col-auto me-auto">
                                                <h6 class="my-0 fw-bold text-dark">Total</h6>
                                            </div>
                                            <div class="col-auto ms-auto">
                                                <h6 class="my-0"><i class="fa fa-currency text-primary" aria-hidden="true"></i></h6>
                                            </div>
                                            <div class="col-12">
                                                <h3 id="revenue-value" class="${formatTextClass(group.total)} my-0 fw-bold">${formatCurrency(group.total)}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card card-dark border border-dark">
                                    <div class="card-body p-4">
                                        <div class="row g-2">
                                            <div class="col-auto me-auto">
                                                <h6 class="my-0 fw-bold text-dark">Total Anual</h6>
                                            </div>
                                            <div class="col-auto ms-auto">
                                                <h6 class="my-0"><i class="fa fa-currency text-primary" aria-hidden="true"></i></h6>
                                            </div>
                                            <div class="col-12">
                                                <h3 id="revenue-value" class="${formatTextClass(group.total_pl)} my-0 fw-bold">${formatCurrency(group.total_pl)}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card card-dark border border-dark">
                                    <div class="card-body p-4">
                                        <div class="row g-2">
                                            <div class="col-auto me-auto">
                                                <h6 class="my-0 fw-bold text-dark">Total Mensual</h6>
                                            </div>
                                            <div class="col-auto ms-auto">
                                                <h6 class="my-0"><i class="fa fa-currency text-primary" aria-hidden="true"></i></h6>
                                            </div>
                                            <div class="col-12">
                                                <h3 id="revenue-value" class="${formatTextClass(group.total_month)} my-0 fw-bold">${formatCurrency(group.total_month)}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            `;

            if (group.data.length > 0) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm js-bootstrap-table" 
                        data-toggle="table"
                        data-search="true"
                        data-buttons-align="left"
                        data-search-align="left"
    data-show-custom-view="true"
    data-custom-view="customViewFormatter"
    data-show-custom-view-button="true"
                        >
                            <thead class="">
                                <tr class="">
                                    <th data-field="account_code" class="p-3 ">Code</th>
                                    <th data-field="account_name" class="p-3 ">Account</th>
                                    <th data-formatter="formatCurrency" data-field="total" class="p-3 text-end">Monto</th>
                                    <th data-field="percent" class="p-3 text-end">Porcentaje</th>
                                    <th data-field="pr" class="p-3 text-center">%PR</th>
                                    <th data-formatter="formatCurrency" data-field="annual" class="p-3 text-end">Anual</th>
                                    <th data-field="pl" class="p-3 text-end">%PL</th>
                                    <th data-formatter="formatCurrency" data-field="monthly" class="p-3 text-end">Mensual</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                group.data.forEach(row => {
                    html += `
<tr data-amount="${row.amount}" data-account="${row.account_id}">
    <td>${row.account_code}</td>
    <td>${row.account_name}</td>
    <td class="text-end">${formatMoney(row.amount)}</td>
    <td>${row.percent}</td>

    <td class="text-end">
        <input type="number"
               class="form-control card-dark border border-dark text-dark form-control-sm text-end pr-input"
               min="0"
               max="100"
               step="0.01"
               value="${row.pr}">
    </td>

    <td class="text-end anual">${formatMoney(row.annual)}</td>
    <td class="text-end pl">${row.pl}%</td>
    <td class="text-end mensual">${formatMoney(row.monthly)}</td>
</tr>
`;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `<p class="text-muted mb-0">No records</p>`;
            }
            // Total footer
            html += `
                            
                        </div>
                    </div>
                </div>
            `;
        }

        document.getElementById('cards-container').innerHTML = html;

        // Initialize bootstrap-table on newly created tables


    });
    document.getElementById('cards-container').innerHTML = html;
    document.querySelectorAll('.js-bootstrap-table').forEach(table => {
        const $table = $(table);

        $table.bootstrapTable(tableOptions);
        table.addEventListener('input', e => {
            if (e.target.classList.contains('pr-input')) {
                const total_anually = recalcTable(table);
            }
        });

        if (isMobile()) {
            $table.bootstrapTable('toggleCustomView', true);
        }
    });
}

function recalcTable(table) {
    let total_anually = 0;

    const rows = table.querySelectorAll('tbody tr');

    // First pass → calculate Anual + total
    rows.forEach(row => {
        const amount = parseFloat(row.dataset.amount) || 0;
        const pr = parseFloat(row.querySelector('.pr-input').value) || 0;

        const anual = amount * (pr / 100);
        row.querySelector('.anual').innerText = formatMoney(anual);

        row.dataset.anual = anual;
        total_anually += anual;
    });

    // Second pass → %PL and Mensual
    rows.forEach(row => {
        const anual = parseFloat(row.dataset.anual) || 0;

        const pl = total_anually > 0 ? (anual / total_anually) * 100 : 0;
        const mensual = anual / 12;

        row.querySelector('.pl').innerText = pl.toFixed(2) + '%';
        row.querySelector('.mensual').innerText = formatMoney(mensual);
    });

    return total_anually;
}

function buildPrJson(table) {
    const result = [];

    table.querySelectorAll('tbody tr').forEach(row => {
        const accountId = row.dataset.account;
        const pr = parseFloat(
            row.querySelector('.pr-input')?.value
        ) || 0;

        result.push({
            account_id: Number(accountId),
            percent: pr
        });
    });

    return result;
}

document.addEventListener('DOMContentLoaded', function () {
    $('#month-filter, #year-filter').on('change', function () {
        initRequest();
    });
});

function customViewFormatter(data) {
    const template = document.getElementById('tableTemplate').innerHTML;
    let html = '<div class="list-group">';

    data.forEach(row => {
        console.log(row);
        let card = template
            .replace('%title%', row.account_name)
            .replace('%code%', row.account_code)
            .replace('%amount%', row.total > 0 ? formatMoney(row.total) : "0.00")

        html += card;
    });

    html += '</div>';
    return html;
}

document.getElementById("save").addEventListener("click", saveFile);

function saveFile() {
    let complete = [];
    let data_url = document.getElementById("data_url_save").value;

    document.querySelectorAll('.js-bootstrap-table').forEach(table => {
        const prJson = buildPrJson(table);
        complete.push(...prJson);
    });

    const year = document.getElementById('year-filter')?.value || new Date().getFullYear();
    const token = localStorage.getItem('finance_auth_token');
    if (!token) return;
    fetch(data_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            year: year,
            data: complete
        })
    })
    .then(res => res.json())
    .then(res => {
        console.log(res);
        // alert('PR saved successfully');
        showAlert("Archivo guardado","Se han actualizado correctamente los datos","","success")
    })
    .catch(err => {
        console.error(err);
        alert('Error saving PR');
    });
}

document.getElementById("refresh").addEventListener("click", initRequest);
window.customViewFormatter = customViewFormatter
