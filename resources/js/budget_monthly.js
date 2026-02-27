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
        let colClass = 'col-12 col-md-12 col-lg-6 col-xl-6 col-xxl-3';
        if (group.display === 'operation' ) {
            colClass = 'col-12 col-md-12 col-lg-6 col-xl-6 col-xxl-3';
        }else if(group.display === 'total'){
            colClass = 'col-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6';
        }
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

        document.getElementById('cards-header').innerHTML = html;
    });
    document.getElementById('cards-header').innerHTML = html;
}

function buildCards(data) {
    let html = '';
    data.forEach(group => {
        const colClass = group.display === 'operation' ?
            'col-12 col-md-12 col-lg-6 col-xl-12 col-xxl-6' :
            'col-12';
        if (group.display === 'operation') {
            html += `
                <div class="${colClass} ">
                    <div class="">
                        <div class="">
                        
                        
                        <div class="fs-5 fw-bold"><i class="me-3 text-primary fs-4 fa-solid ${group.icon}" aria-hidden="true">
                        </i>${group.title}</div>
            `;

            if (group.data.length > 0) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm js-bootstrap-table" 
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
                                    <th data-field="amount_budget" class="p-3 text-end">Presupuesto</th>
                                    <th data-field="amount" class="p-3 text-end">Real</th>
                                    <th data-field="amount_difference" class="p-3 text-end">Diferencia</th>
                                    <th data-field="percent" class="p-3 text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                group.data.forEach(row => {
                    html += `
                        <tr>
                            <td>${row.account_code}</td>
                            <td>${row.account_name}</td>
                            <td class="text-end">${formatCurrency(row.amount_budget)}</td>
                            <td class="text-end">${formatCurrency(row.amount)}</td>
                            <td class="text-end">${formatCurrency(row.amount_difference)}</td>
                            <td class="text-end">${formatMoney(row.percent)}</td>
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
tableOptions.onCustomViewPostBody = function (data) {
    console.log("post");
    const container = document.querySelector('#cards-container');

if (container) {
    new Masonry(container, {
        percentPosition: true
    });
}
};
    $table.bootstrapTable(tableOptions);

    if (isMobile()) {
        $table.bootstrapTable('toggleCustomView', true);
    }
    
    const container = document.querySelector('#cards-container');

if (container) {
    new Masonry(container, {
        percentPosition: true
    });
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
    let html = '<div class="list-group card-dark">';

    data.forEach(row => {
        let card = template
            .replace('%title%', row.account_name)
            .replace('%code%', row.account_code)
            .replace('%amount%',formatCurrency(row.amount))
            .replace('%amount_difference%',formatCurrency(row.amount_difference))
            .replace('%amount_budget%',formatCurrency(row.amount_budget))
            .replace('%percent_bar%', `style="width: ${row.percent}%"`)
            .replace('%difference_class%',formatBadgeClass(row.amount_difference))
            .replace('%percent%', (row.percent));

        html += card;
    });

    html += '</div>';
    
    return html;
}
document.getElementById("refresh").addEventListener("click", initRequest);
window.customViewFormatter=customViewFormatter