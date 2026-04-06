// --- 1. Global State Management ---
let globalState = {
    data: [],
    cash_accounts: [],
    saved: []
};
let masonryInstance = null;

// --- 2. Initial Data Fetch ---
function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    let data_url = document.getElementById("data_url").value;
    const url = new URL(data_url);

    const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;
    const checkbox = document.getElementById('detailsCheckbox');

    if (month) url.searchParams.set('month', month);
    if (year) url.searchParams.set('year', year);
    url.searchParams.set('details', checkbox.checked ? 'true' : 'false');

    fetch(url, {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
        })
        .then(res => res.json())
        .then(data => {
            globalState.data = data.data;
            globalState.saved = data.save; // Make sure this matches your JSON payload exactly
            globalState.cash_accounts = data.cash_accounts;
            buildHeaderCards(globalState.data);
            buildCards(globalState.data, globalState.cash_accounts, globalState.saved);
            initObservers();
        })
        .catch((error) => {
            console.error("Failed to fetch initial data:", error);
        });
}

initRequest();

// --- Header Cards Builder ---
function buildHeaderCards(data) {
    let html = '';
    data.forEach(group => {
        if (group.display === 'operation') return;
        let colClass = 'col-12 col-md-6 col-lg-6 col-xl-4 col-xxl-4';
        if (group.display === 'total') {
            colClass = 'col-12 col-md-12 col-lg-12 col-xl-4 col-xxl-4';
        }
        html += `
            <div class="${colClass}">
                <div class="card card-dark border border-dark bg-dark h-100">
                    <div class="card-body p-4 text-dark">
                        <div class="row g-2">
                            <div class="col-auto me-auto"><h6 class="my-0 fw-bold">${group.title}</h6></div>
                            <div class="col-auto ms-auto"><h6 class="my-0"><i class="fa ${group.icon} text-primary"></i></h6></div>
                            <div class="col-12"><h6 class="text-muted my-0 fw-light">${group.description}</h6></div>
                           <div class="col-auto d-flex flex-column me-2">
                                <small class="text-muted">Total</small>
                                <h5 class="${formatTextClass(group.total_sum)} my-0 fw-bold">
                                    ${formatCurrency(group.total_sum)}
                                </h5>
                            </div>
                            <div class="col-auto ms-auto d-flex flex-column">
                                <small class="text-muted">Total proyectado</small>
                                <h5 class="${formatTextClass(group.total_projection)} my-0 fw-bold">
                                    ${formatCurrency(group.total_projection)}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    });
    document.getElementById('cards-header').innerHTML = html;
}

// --- NEW FIX: Sync DOM values to State before re-rendering ---
function syncDOMToState() {
    const rows = document.querySelectorAll('tr[data-group-key]');
    rows.forEach(tr => {
        const groupKey = tr.getAttribute('data-group-key');
        const manualId = tr.getAttribute('data-manual-id');
        const accountId = tr.getAttribute('data-account-id');
        
        const group = globalState.data.find(g => g.key === groupKey);
        if (!group) return;

        // Find the specific row object in memory
        let dataRow = null;
        if (manualId) {
            dataRow = group.data.find(r => r.manualId === manualId);
        } else if (accountId) {
            dataRow = group.data.find(r => r.account_id == accountId);
        }

        if (!dataRow) return;

        // 1. Grab current values typed into inputs
        const nameInput = tr.querySelector('.manual-name');
        if (nameInput) dataRow.account_name = nameInput.value;

        const totalInput = tr.querySelector('.manual-total');
        if (totalInput) dataRow.total = parseFloat(totalInput.value) || 0;

        const projInput = tr.querySelector('.manual-projection');
        if (projInput) dataRow.projection = parseFloat(projInput.value) || 0;

        const cashSelect = tr.querySelector('.cash-select');
        if (cashSelect) dataRow.cash_account = cashSelect.value !== "null" ? parseInt(cashSelect.value) : null;
        
        // 2. Also sync to saved_data array if it exists there, since buildCards reads from it first
        if (globalState.saved) {
            let savedRow = null;
            if (accountId) {
                savedRow = globalState.saved.find(s => s.id == accountId && s.key === groupKey);
            } else if (dataRow.account_name) {
                savedRow = globalState.saved.find(s => s.id == null && s.key === groupKey && s.name === dataRow.account_name);
            }
            if (savedRow) {
                if (totalInput) savedRow.total = dataRow.total;
                if (projInput) savedRow.projection = dataRow.projection;
                if (cashSelect) savedRow.cash_account = dataRow.cash_account;
                if (nameInput) savedRow.name = dataRow.account_name;
            }
        }
    });
}

// --- 3. Add/Delete Functions ---
function addManual(groupKey) {
    // REMOVED saveData() here to prevent full page reloads and let users type locally.
    // Instead, we sync what they just typed into memory.
    syncDOMToState(); 

    const group = globalState.data.find(g => g.key === groupKey);
    if (!group) return;

    const scrollPosition = window.scrollY;
    const manualId = 'manual-' + Date.now();
    const newRecord = { isNew: true, manual_id: manualId, manualId, key: group.key, account_id: null, account_name: "Cuenta Disponible", total: 0, account_code: "MANUAL", percent: 0, percent_projection: 0, opening: 0, debit: 0, credit: 0, projection: 0 };
    group.data.push(newRecord);
    globalState.saved.push(newRecord);
    buildCards(globalState.data, globalState.cash_accounts, globalState.saved);
    saveDataSilent();
     moveToID(manualId);
    // window.scrollTo(0, scrollPosition);
}

function deleteManual(groupKey, manualId) {
    // Sync typed text before deleting so other rows don't lose their data
    syncDOMToState();
    const group = globalState.data.find(g => g.key === groupKey);
    const savedEntry = globalState.saved.findIndex(g => g.manual_id === manualId);
    if(manualId != null){
globalState.saved.splice(savedEntry,1);
    }
    if (!group) return;
    const scrollPosition = window.scrollY;
    const rowIndex = group.data.findIndex(row => row.manualId === manualId);
    if (rowIndex > -1) {
        group.data.splice(rowIndex, 1);
    }
    moveToID(manualId, true);
    buildCards(globalState.data, globalState.cash_accounts, globalState.saved);
    saveDataSilent();

}

// --- 4. The `buildCards` Rendering Function ---
function buildCards(data, cash_accounts, saved_data) {
    if (masonryInstance) masonryInstance.destroy();
    document.querySelectorAll('.js-bootstrap-table').forEach(table => $(table).bootstrapTable('destroy'));

    // --- PRE-PROCESSING: Inject saved manual rows into the data arrays ---
    if (saved_data && saved_data.length > 0) {
        saved_data.forEach(savedRow => {
            if (savedRow.id === null) {
                let group = data.find(g => g.key === savedRow.key);
                if (group && group.manual) {
                    let exists = group.data.find(r => r.isNew && r.account_name === savedRow.name);
                    if (!exists) {
                        let manual_id = savedRow.manual_id ?? 'saved-manual-' + Math.random().toString(36).substr(2, 9);
                        group.data.push({
                            isNew: true,
                            manualId: manual_id,
                            key: group.key,
                            account_id: null,
                            account_name: savedRow.name,
                            total: savedRow.total,
                            account_code: savedRow.code || "MANUAL",
                            percent: 0,
                            percent_projection: 0,
                            projection: savedRow.projection
                        });
                    }
                }
            }
        });
    }

    let html = '';
    data.forEach(group => {
        if (group.display !== 'operation') return;

        let addManualButton = group.manual ? `<button onclick="addManual('${group.key}')" class="btn btn-primary btn-sm"><i class="fas fa-add"></i></button>` : '';

        const actionsHeader = group.manual ? `<th class="p-3" data-field="actions"></th>` : '';
        const projectionHeader = `<th class="p-3 text-end" data-field="projection">Estimación</th>`;
        const percentTotalHeader = `<th class="p-3 text-end" data-field="percent_projection">P % Total</th>`;
        const cashAccountHeader = group.projection_manual ? `<th class="p-3" data-field="cash_account">Cuenta Efectivo</th>` : '';
        const amountProjectionHeader = group.show_amount_projection ? `<th class="p-3 text-end" data-field="amount_projection">Saldo Proyectado</th>` : '';

        html += `
            <div class="col-12 grid-item" id="${group.id_stop ?? null}">
                <div class="fs-5 fw-bold mb-2">${addManualButton} ${group.title}</div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card card-dark border border-dark">
                            <div class="card-body p-4">
                                <div class="row g-2">
                                    <div class="col-auto d-flex flex-column me-2">
                                        <small class="text-muted">Total</small>
                                        <h3 class="${formatTextClass(group.total_sum)} my-0 fw-bold">
                                            ${formatCurrency(group.total_sum)}
                                        </h3>
                                    </div>
                                    <div class="col-auto ms-auto d-flex flex-column">
                                        <small class="text-muted">Total proyectado</small>
                                        <h3 class="${formatTextClass(group.total_projection)} my-0 fw-bold">
                                            ${formatCurrency(group.total_projection)}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

        if (group.data.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-sm js-bootstrap-table" data-toggle="table">
                        <thead>
                            <tr>
                                ${actionsHeader}
                                <th class="p-3" data-field="account_name">Cuenta</th>
                                <th class="p-3 text-end" data-field="total">Monto</th>
                                ${amountProjectionHeader}
                                <th class="p-3 text-end" data-field="percent">%</th>
                                ${projectionHeader}
                                ${percentTotalHeader}
                                ${cashAccountHeader}
                            </tr>
                        </thead>
                        <tbody>`;

            group.data.forEach(row => {
                let amount = row[group.total_attribute];
                if (amount === undefined) amount = row['total'] || 0;
                

                let savedRow = null;
                if (saved_data) {
                    if (row.account_id) {
                        savedRow = saved_data.find(s => s.id == row.account_id && s.key === group.key);
                    } else {
                        savedRow = saved_data.find(s => s.id == null && s.key === group.key && s.name === row.account_name);

                    }
                }

                // --- 2. EXTRACT VALUES (MODIFIED TO SUPPORT SYNCED UI TEXT) ---
                // let valTotal = savedRow ? savedRow.total : (row.total !== undefined && row.isNew ? row.total : amount);
                let valTotal = amount;
                // let valTotal = savedRow ? savedRow[group.total_attribute] : (row[group.total_attribute] !== undefined && row.isNew ? row[group.total_attribute] : 0);

                let valPercProj = savedRow ? savedRow.percent_projection : (row.percent_projection || 0);
                let valPerc = savedRow ? savedRow.percent : (row.percent || 0);


                // if(group.show_amount_projection){
                //     console.log(row);
                //     console.log(amount);
                //     console.log(valTotal);
                //     console.log(savedRow.total);
                //     console.log(savedRow);
                //     console.log(row.total);
                // }
                
                // Fallback priority: 1. Server Save -> 2. Synced UI Typings -> 3. Backend array default
                let valProj = 0;
                let valProjAmount = row.amount_projection;
                
                if (savedRow) {
                    valProj = savedRow.projection;
                } else if (row.projection !== undefined) {
                    valProj = row.projection; // Grab the typed projection we synced
                } else {
                    valProj = row.projection_entry || 0;
                }
                
                let valCash = savedRow ? savedRow.cash_account : (row.cash_account !== undefined ? row.cash_account : null);


                // --- 3. DYNAMIC CASH ACCOUNT SELECT ---
                let row_cash_select = `<select class="form-control cash-select">
                                        <option value="null" ${valCash == null ? 'selected' : ''}>Sin Cuenta</option>`;
                cash_accounts.forEach(account => {
                    let isSelected = (valCash == account.id) ? 'selected' : '';
                    row_cash_select += `<option value="${account.id}" ${isSelected}>${account.name}</option>`;
                });
                row_cash_select += `</select>`;

                // --- 4. BUILD CELLS ---
                let actionsCell = '';
                let nameCell = `<td>${row.account_name}</td>`;
                let totalCell = `<td class="text-end">${formatCurrency(valTotal)}</td>`;

                if (row.isNew) {
                    nameCell = `<td><input type="text" class="form-control manual-name" value="${row.account_name}"></td>`;
                    totalCell = `<td><input type="number" class="form-control manual-total" value="${valTotal}"></td>`;
                }

                if (group.manual) {
                    const deleteButton = row.isNew ? `<button onclick="deleteManual('${group.key}', '${row.manualId}')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>` : '';
                    actionsCell = `<td>${deleteButton}</td>`;
                }

                let percentTotalCell = `<td class="text-end">${formatMoney(row.percent_projection)}</td>`;
                let percentCell = ` <td class="text-end">${formatMoney(row.percent)}</td>`;
                let projectionCell = '';

                if (group.manual) {
                    projectionCell = `<td><input type="number" class="form-control manual-projection" value="${valProj}"></td>`;
                    percentTotalCell = `<td class="text-end">${formatMoney(valPercProj)}</td>`;
                    percentCell = ` <td class="text-end">${formatMoney(valPerc)}</td>`;
                } else if (group.projection_manual) {
                    projectionCell = `<td><input type="number" class="form-control manual-projection" value="${valProj}"></td>`;
                } else {
                    projectionCell = `<td class="text-end">${formatCurrency(valProj)}</td>`;
                }


                const cashAccountCell = group.projection_manual ? `<td class="text-end">${row_cash_select}</td>` : '';
                const projectionAmountCell = group.show_amount_projection ? `<td class="text-end">${valProjAmount}</td>` : '';

                // html += `
                //     <tr id="${row.manualId || ''}" data-group-key="${group.key}" 
                //         data-manual-id="${row.manualId || ''}"
                //         data-account-id="${row.account_id || ''}"
                //         data-account-name="${row.account_name || ''}"
                //         data-account-code="${row.account_code || ''}"
                //         data-total="${valTotal}">
                //         ${actionsCell}
                //         ${nameCell}
                //         ${totalCell}
                //         ${percentCell}
                //         ${projectionCell}
                //         ${percentTotalCell}
                //         ${cashAccountCell}
                //     </tr>`;
                // console.log(projectionAmountCell);
                html += `
                    <tr class="${row.hidden ? 'd-none': ''}" id="${row.manualId || ''}" data-group-key="${group.key}" 
                        data-manual-id="${row.manualId || ''}"
                        data-account-id="${row.account_id || ''}"
                        data-account-name="${row.account_name || ''}"
                        data-account-code="${row.account_code || ''}"
                        data-total="${valTotal}">
                        ${actionsCell}
                        ${nameCell}
                        ${totalCell}
                        ${projectionAmountCell}
                        ${percentCell}
                        ${projectionCell}
                        ${percentTotalCell}
                        ${cashAccountCell}
                    </tr>`;
            });

            html += `</tbody></table></div>`;
        } else {
            html += `<p class="text-muted mb-0">Sin registros para este grupo</p>`;
        }
        html += `</div>`;
    });

    const container = document.getElementById('cards-container');
    container.innerHTML = html;
    document.querySelectorAll('.js-bootstrap-table').forEach(table => $(table).bootstrapTable());
    
    setTimeout(() => {
        masonryInstance = new Masonry(container, { itemSelector: '.grid-item', percentPosition: true });
    }, 100);
}

// --- 5. Save Functionality ---
function saveData() {
    const entriesToSave = [];
    let flag = 0;
    const rows = document.querySelectorAll('tr[data-group-key]');

    rows.forEach(row => {
        row.classList.remove("error_cash_account");
        const groupKey = row.getAttribute('data-group-key');
        const group = globalState.data.find(g => g.key === groupKey);
        if (!group) return;

        if (!group.manual && !group.projection_manual) return;

        const isNew = row.getAttribute('data-manual-id') !== "";
        let entry = null;

        if (group.manual) {
            if (isNew) {
                const nameInput = row.querySelector('.manual-name');
                const totalInput = row.querySelector('.manual-total');
                const projInput = row.querySelector('.manual-projection');
                entry = {
                    key: group.key,
                    id: null,
                    name: nameInput ? nameInput.value : 'N/A',
                    manual_id: row.getAttribute('data-manual-id') || '',
                    total: totalInput ? parseFloat(totalInput.value) || 0 : 0,
                    projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                    code: "",
                    row: row
                };
            }
        } else if (group.projection_manual) {
            const projInput = row.querySelector('.manual-projection');
            entry = {
                key: group.key,
                id: parseInt(row.getAttribute('data-account-id')) || null,
                name: row.getAttribute('data-account-name') || '',
                manual_id: row.getAttribute('data-manual-id') || '',
                total: parseFloat(row.getAttribute('data-total')) || 0,
                projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                code: row.getAttribute('data-account-code') || '',
                row: row
            };
        }

        if (entry) {
            
            const cashSelect = row.querySelector('.cash-select');
            if (cashSelect) {
                const selectedVal = cashSelect.value;
                if (selectedVal !== "null" && selectedVal !== null && selectedVal !== "") {
                    entry.cash_account = parseInt(selectedVal);
                } else {
                    entry.cash_account = null;
                }
            }
            if(entry.projection > 0 && entry.cash_account == null){
                // console.log(entry.row);
                flag++;
                showAlert("Cuenta no asignada a Cuenta Efectivo", `La cuenta ${entry.name} no tiene una Cuenta Efectivo asignada` , "", "danger");
                // return;
            }
            entriesToSave.push(entry);
        }
    });

    entriesToSave.forEach(entry => {
        if(entry.projection > 0 && entry.cash_account == null){
            console.log(entry.row);
            entry.row.classList.add("error_cash_account");
        }
    });

    if(flag == 0){
        console.log("--- Data to Save ---", entriesToSave);


        let data_url = document.getElementById("data_url_save").value;
        const month = document.getElementById('month-filter')?.value;
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
                month: month,
                year: year,
                data: entriesToSave
            })
        })
        .then(res => res.json())
        .then(res => {
            if(typeof showAlert === "function") showAlert("Archivo guardado", "Se han actualizado correctamente los datos", "", "success");
            initRequest(); // Resync state with backend
        })
        .catch(err => {
            console.error(err);
            if(typeof showAlert === "function") showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger");
        });
    }
}

// --- 5. Save Functionality ---
function saveDataSilent() {
    const entriesToSave = [];
    const rows = document.querySelectorAll('tr[data-group-key]');
    let flag = 0;

    rows.forEach(row => {
        const groupKey = row.getAttribute('data-group-key');
        const group = globalState.data.find(g => g.key === groupKey);
        if (!group) return;

        if (!group.manual && !group.projection_manual) return;

        const isNew = row.getAttribute('data-manual-id') !== "";
        let entry = null;

        if (group.manual) {
            if (isNew) {
                const nameInput = row.querySelector('.manual-name');
                const totalInput = row.querySelector('.manual-total');
                const projInput = row.querySelector('.manual-projection');
                entry = {
                    key: group.key,
                    id: null,
                    name: nameInput ? nameInput.value : 'N/A',
                    manual_id: row.getAttribute('data-manual-id') || '',
                    total: totalInput ? parseFloat(totalInput.value) || 0 : 0,
                    projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                    code: ""
                };
            }
        } else if (group.projection_manual) {
            const projInput = row.querySelector('.manual-projection');
            entry = {
                key: group.key,
                id: parseInt(row.getAttribute('data-account-id')) || null,
                name: row.getAttribute('data-account-name') || '',
                manual_id: row.getAttribute('data-manual-id') || '',
                total: parseFloat(row.getAttribute('data-total')) || 0,
                projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                code: row.getAttribute('data-account-code') || ''
            };
        }

        if (entry) {
            console.log(entry);
            const cashSelect = row.querySelector('.cash-select');
            if (cashSelect) {
                const selectedVal = cashSelect.value;
                if (selectedVal !== "null" && selectedVal !== null && selectedVal !== "") {
                    entry.cash_account = parseInt(selectedVal);
                } else {
                    entry.cash_account = null;
                }
            }
            if(entry.projection > 0 && entry.cash_account == null){
                console.log(entry);
                flag++;
                showAlert("Cuenta no asignada a Cuenta Efectivo", `La cuenta ${entry.name} no tiene una Cuenta Efectivo asignada` , "", "danger");
                return;
            }
            entriesToSave.push(entry);
        }
    });

    if(flag == 0){


    let data_url = document.getElementById("data_url_save").value;
    const month = document.getElementById('month-filter')?.value;
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
            month: month,
            year: year,
            data: entriesToSave
        })
    })
    .then(res => res.json())
    .then(res => {
        console.log(res);
    })
    .catch(err => {
        // console.error(err);
        // if(typeof showAlert === "function") showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger");
    });
    }

}

// --- 6. Event Listeners & Helpers ---
document.addEventListener('DOMContentLoaded', function() {
    $('#month-filter, #year-filter, #detailsCheckbox').on('change', initRequest);
    document.getElementById("refresh").addEventListener("click", initRequest);
    document.getElementById("save").addEventListener("click", saveData);
});

window.addManual = addManual;
window.deleteManual = deleteManual;


function initObservers() {
    const cardsHeader = document.getElementById('cards-header');
    const floatingHeader = document.getElementById('floating-header');
    const floatingContent = document.getElementById('floating-content');
    const footerCard = document.getElementById('footer-card');
    footerCard.innerHTML="";
    const firstCard = cardsHeader.children[0];
    const secondCard = cardsHeader.children[1];
    const thirdCard = cardsHeader.children[2].cloneNode(true);
    thirdCard.className = "col-12"

    footerCard.appendChild(thirdCard);

    const stopIncomes = document.getElementById('stop_incomes');
    const stopExpenses = document.getElementById('stop_expenses');

    if (!stopIncomes || !stopExpenses) return;

    // HEADER OBSERVER
    const headerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {

        const rect = entry.boundingClientRect;

        // Only hide if header is still below sticky line
        if (rect.bottom > 60) {
            floatingHeader.classList.add('d-none');
        } else {
            floatingHeader.classList.remove('d-none');
        }

    });
}, {
    threshold: 0,
    rootMargin: '-60px 0px 0px 0px'
});

    headerObserver.observe(cardsHeader);

    // SECTION OBSERVER
    const sectionObserver = new IntersectionObserver((entries) => {

    let visibleEntry = entries.find(entry => entry.isIntersecting);

    if (!visibleEntry) return;

    floatingContent.innerHTML = '';

    if (visibleEntry.target.id === 'stop_incomes') {
        floatingContent.appendChild(prepareCard(firstCard));
    }

    if (visibleEntry.target.id === 'stop_expenses') {
        floatingContent.appendChild(prepareCard(secondCard));
    }

}, {
    root: null,
    threshold: 0,
    rootMargin: '-60px 0px -90% 0px'
});

    sectionObserver.observe(stopIncomes);
    sectionObserver.observe(stopExpenses);
}

function prepareCard(card) {
    const clone = card.cloneNode(true);

    clone.className = 'col-12';
    clone.querySelector(".card").classList.add("shadow");

    return clone;
}

