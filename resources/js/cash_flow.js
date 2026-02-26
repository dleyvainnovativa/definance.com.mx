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
            console.log(data);
            buildHeaderCards(data.map);
            buildCards(data.data);
            // params.success(data);
        })
        .catch((error) => {
            console.log(error);
        });
}
initRequest();

document.addEventListener('DOMContentLoaded', function () {
    $('#month-filter, #year-filter').on('change', function () {
        initRequest();
    });
});

function buildHeaderCards(data) {
    let html = '';
    data.forEach(group => {
        const colClass = 'col-12 col-md-12 col-lg-6 col-xl-3'
            html += `

<div class="${colClass}">
        <div class="card card-dark border border-dark h-100 bg-dark">
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

function buildCards(data){
    const container = document.getElementById('cards-container');
container.innerHTML = "";

data.forEach(section => {


    const card = document.createElement('div');
    card.className = 'col-12 col-md-6 col-xl-4';

    card.innerHTML = `
        <div class="card card-dark border border-dark h-100 shadow-sm">
            <div class="card-body p-4 text-dark">

                <!-- Header -->
                <div class="mb-3 fs-4 fw-bold ${formatTextClass(section.total)}">
                    ${formatCurrency(section.total)}
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid ${section.icon} fa-lg me-4"></i>
                    <h5 class="mb-0 fw-bold">${section.title}</h5>
                </div>

                <!-- Total -->

                <!-- Details -->
                <ul class="list-group list-group-flush">
                    ${
                        section.data.length
                        ? section.data.map(item => `
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span class="small">${item.title}</span>
                                    <span class="fw-semibold">
                                        ${formatCurrency(item.total)}
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    ${item.code_target} – ${item.code_target_end}
                                </div>
                            </li>
                        `).join('')
                        : `<li class="list-group-item px-0 text-muted fst-italic">
                                Sin movimientos
                           </li>`
                    }
                </ul>

            </div>
        </div>
    `;

    container.appendChild(card);
});
}
document.getElementById("refresh").addEventListener("click", initRequest);
