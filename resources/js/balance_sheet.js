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
            buildCards(data.data);
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
        const colClass = 'col-12 col-md-12 col-lg-6 col-xl-3'
        if (group.display === 'operation') {
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

function buildCards(data) {
    let html = '';
    data.forEach(group => {
        const colClass = group.display === 'operation' ?
            'col-12 col-md-12 col-lg-12 col-xl-6' :
            'col-12';
        if (group.display === 'operation') {
            html += `
                <div class="${colClass} ">
                    <div class="card card-dark border border-dark shadow-sm h-100 text-dark">
                        <div class="card-body p-4">
                        
                            
                        <div class="mb-3 fs-5 fw-bold"><i class="me-3 text-primary fs-4 fa-solid ${group.icon}" aria-hidden="true">
                        </i>${group.title}</div>
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
                                    <th data-field="account_code" class="">Code</th>
                                    <th data-field="account_name" class="">Account</th>
                                    <th data-field="amount" class="text-end">Monto</th>
                                    <th data-field="percent" class="text-end">%</th>
                                    <th data-field="percent_group" class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                group.data.forEach(row => {
                    html += `
                        <tr>
                            <td>${row.account_code}</td>
                            <td>${row.account_name}</td>
                            <td class="text-end">${formatMoney(row.amount)}</td>
                            <td class="text-end">${formatMoney(row.percent)}</td>
                            <td class="text-end">${formatMoney(row.percent_group)}</td>
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
                            <div class="mt-3 text-end">
                                <strong>Total:</strong>
                                <span class="ms-2">${formatMoney(group.total)}</span>
                            </div>
                            <div class="mt-1 text-end">
                                <strong>Porcentaje:</strong>
                                <span class="ms-2">${formatMoney(group.percent)}%</span>
                            </div>
                            <div class="mt-1 text-end">
                                <strong>Porcentaje Totales:</strong>
                                <span class="ms-2">${formatMoney(group.percent_group)}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (group.display === 'total') {
            html += `

        <div class="${colClass}">
        <div class="text-bg-white border border-dark card card-dark h-100 position-relative text-dark">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">

                    <!-- Leading icon -->
                    <div class="me-3 text-primary fs-4">
                        <i class="fa-solid ${group.icon}" aria-hidden="true"></i>
                    </div>

                    <!-- Title + subtitle -->
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-5">${group.title}</div>
                    </div>

                    <!-- Trailing amount -->
                    <div class="ms-3 fw-semibold">
                        <div class="col-12 text-dark text-end fs-5">
                            ${group.total}
                        </div>
                        <div class="col-12 text-muted text-end fs-5">
                            ${group.percent}%
                        </div>
                    </div>
                </div>
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

    if (isMobile()) {
        $table.bootstrapTable('toggleCustomView', true);
    }
});
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
            .replace('%amount%', row.amount > 0 ? formatMoney(row.amount) : "0.00")
            .replace('%percent%', row.percent > 0 ? formatMoney(row.percent) : "0.00")
            .replace('%percent_group%', row.percent > 0 ? formatMoney(row.percent) : "0.00");

        html += card;
    });

    html += '</div>';
    return html;
}
document.getElementById("refresh").addEventListener("click", initRequest);
window.customViewFormatter=customViewFormatter