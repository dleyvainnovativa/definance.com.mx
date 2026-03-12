// --- 1. Global State Management ---
let globalState = {
    data: [],
    cash_accounts: []
};
let masonryInstance = null;

// --- 2. Initial Data Fetch ---
function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    let data_url = document.getElementById("data_url").value;
    const url = new URL(data_url);
    const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;
    if (month) url.searchParams.set('month', month);
    if (year) url.searchParams.set('year', year);
    fetch(url, {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
        })
        .then(res => res.json())
        .then(data => {
            globalState.data = data.data;
            globalState.saved = data.save;
            globalState.cash_accounts = data.cash_accounts;
            buildHeaderCards(globalState.data);
            buildCards(globalState.data, globalState.cash_accounts, globalState.saved);
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
            </div>`;
    });
    document.getElementById('cards-header').innerHTML = html;
}

// --- 3. Add/Delete Functions ---
function addManual(groupKey) {
    const group = globalState.data.find(g => g.key === groupKey);
    if (!group) return;
    const scrollPosition = window.scrollY;
    const manualId = 'manual-' + Date.now();
    const newRecord = { isNew: true, manualId, key: group.key, account_id: null, account_name: "Cuenta Disponible", total: 0, account_code: "MANUAL", percent: 0, percent_projection: 0, opening: 0, debit: 0, credit: 0 };
    group.data.push(newRecord);
    buildCards(globalState.data, globalState.cash_accounts, globalState.saved);
    window.scrollTo(0, scrollPosition);
}

function deleteManual(groupKey, manualId) {
    const group = globalState.data.find(g => g.key === groupKey);
    if (!group) return;
    const scrollPosition = window.scrollY;
    const rowIndex = group.data.findIndex(row => row.manualId === manualId);
    if (rowIndex > -1) {
        group.data.splice(rowIndex, 1);
    }
    buildCards(globalState.data, globalState.cash_accounts,globalState.saved);
    window.scrollTo(0, scrollPosition);
}

// --- 4. The `buildCards` Rendering Function ---
// --- 4. The `buildCards` Rendering Function ---
function buildCards(data, cash_accounts, saved_data) {
    // console.log("Saved Data:", saved_data);
    
    if (masonryInstance) masonryInstance.destroy();
    document.querySelectorAll('.js-bootstrap-table').forEach(table => $(table).bootstrapTable('destroy'));

    // --- PRE-PROCESSING: Inject saved manual rows into the data arrays ---
    // If we have manual entries saved (id is null), we need to add them to the correct group 
    // so they are rendered in the table on page load.
    if (saved_data && saved_data.length > 0) {
        saved_data.forEach(savedRow => {
            if (savedRow.id === null) {
                let group = data.find(g => g.key === savedRow.key);
                if (group && group.manual) {
                    // Check if it already exists to avoid duplicates during re-renders
                    let exists = group.data.find(r => r.isNew && r.account_name === savedRow.name);
                    if (!exists) {
                        group.data.push({
                            isNew: true, // Flags it as a manual row to get inputs and delete button
                            manualId: 'saved-manual-' + Math.random().toString(36).substr(2, 9),
                            key: group.key,
                            account_id: null,
                            account_name: savedRow.name,
                            total: savedRow.total,
                            account_code: savedRow.code || "MANUAL",
                            percent: 0,
                            percent_projection: 0
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
        const projectionHeader = `<th class="p-3 text-end" data-field="projection">Proyección</th>`;
        const percentTotalHeader = `<th class="p-3 text-end" data-field="percent_projection">P % Total</th>`;
        const cashAccountHeader = group.projection_manual ? `<th class="p-3" data-field="cash_account">Cuenta Efectivo</th>` : '';

        html += `
            <div class="col-12 grid-item">
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
                </div>
                
            `;

        if (group.data.length > 0) {
            html += `
                <div class="table-responsive">
                    <table class="table table-sm js-bootstrap-table" data-toggle="table">
                        <thead>
                            <tr>
                                ${actionsHeader}
                                <th class="p-3" data-field="account_name">Cuenta</th>
                                <th class="p-3 text-end" data-field="total">Monto</th>
                                <th class="p-3 text-end" data-field="percent">%</th>
                                ${projectionHeader}
                                ${percentTotalHeader}
                                ${cashAccountHeader}
                            </tr>
                        </thead>
                        <tbody>`;

            group.data.forEach(row => {
                // Get base amount
                let amount = row[group.total_attribute];
                if (amount === undefined) amount = row['total'] || 0;

                // --- 1. FIND MATCHING SAVED DATA ---
                let savedRow = null;
                if (saved_data) {
                    if (row.account_id) {
                        // Match normal rows by ID
                        savedRow = saved_data.find(s => s.id == row.account_id && s.key === group.key);
                    } else {
                        // Match manual rows by name
                        savedRow = saved_data.find(s => s.id == null && s.key === group.key && s.name === row.account_name);
                    }
                }

                // --- 2. EXTRACT VALUES ---
                let valTotal = savedRow ? savedRow.total : amount;
                // let valProj = savedRow ? savedRow.projection : (0);
                let valPercProj = savedRow ? savedRow.percent_projection : (row.projection_percent || 0);
                let valPerc = savedRow ? savedRow.percent : (row.projection_percent || 0);
                let valProj = savedRow ? savedRow.projection : 0;
                // let valProj = savedRow ? savedRow.projection : (row.projection_entry || 0);
                let valCash = savedRow ? savedRow.cash_account : null;

                // --- 3. DYNAMIC CASH ACCOUNT SELECT ---
                // Rebuild the select per row to properly attach the "selected" attribute
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

                // If it is a manual row (new or loaded from save), show Inputs
                if (row.isNew) {
                    nameCell = `<td><input type="text" class="form-control manual-name" value="${row.account_name}"></td>`;
                    totalCell = `<td><input type="number" class="form-control manual-total" value="${valTotal}"></td>`;
                }

                // Delete button for manual groups
                if (group.manual) {
                    const deleteButton = row.isNew ? `<button onclick="deleteManual('${group.key}', '${row.manualId}')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>` : '';
                    actionsCell = `<td>${deleteButton}</td>`;
                }

                let percentTotalCell = `<td class="text-end">${formatMoney(row.percent_projection)}</td>`;
                let percentCell = ` <td class="text-end">${formatMoney(row.percent)}</td>`;
                // Projection logic based on previous rules
                let projectionCell = '';
                if (group.manual) {
                    // Rule: "on the group that has manual attribute as true i need that projection be a number not an input"
                    // projectionCell = `<td class="text-end">${formatCurrency(valProj)}</td>`;
                    projectionCell = `<td><input type="number" class="form-control manual-projection" value="${valProj}"></td>`;
                    percentTotalCell = `<td class="text-end">${formatMoney(valPercProj)}</td>`;
                    percentCell = ` <td class="text-end">${formatMoney(valPerc)}</td>`;
                    
                } else if (group.projection_manual) {
                    // Rule: "set the value of projection on the manual-projection input"
                    projectionCell = `<td><input type="number" class="form-control manual-projection" value="${valProj}"></td>`;
                } else {
                    projectionCell = `<td class="text-end">${formatCurrency(valProj)}</td>`;
                }
                if (group.projection_manual == false) {
                    // console.log(group);
                    valProj = savedRow ? savedRow.projection : (row.projection_entry || 0);
                    projectionCell = `<td class="text-end">${formatCurrency(valProj)}</td>`;
                }

                const cashAccountCell = group.projection_manual ? `<td class="text-end">${row_cash_select}</td>` : '';

                html += `
                    <tr data-group-key="${group.key}" 
                        data-manual-id="${row.manualId || ''}"
                        data-account-id="${row.account_id || ''}"
                        data-account-name="${row.account_name || ''}"
                        data-account-code="${row.account_code || ''}"
                        data-total="${valTotal}">
                        ${actionsCell}
                        ${nameCell}
                        ${totalCell}
                        ${percentCell}
                        ${projectionCell}
                        ${percentTotalCell}
                        ${cashAccountCell}
                    </tr>`;
            });

            html += `</tbody></table></div>`;
        } else {
            html += `<p class="text-muted mb-0">No records for this group.</p>`;
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


// --- 5. Save Functionality (REBUILT) ---
function saveData() {
    const entriesToSave = [];
    
    // We only care about rows we tagged with 'data-group-key'
    const rows = document.querySelectorAll('tr[data-group-key]');
    
    rows.forEach(row => {
        const groupKey = row.getAttribute('data-group-key');
        const group = globalState.data.find(g => g.key === groupKey);
        
        if (!group) return;

        // ONLY target groups that have manual=true OR projection_manual=true
        if (!group.manual && !group.projection_manual) return;

        const isNew = row.getAttribute('data-manual-id') !== "";
        let entry = null;

        if (group.manual) {
            // For manual groups: Only save the newly added rows
            if (isNew) {
                const nameInput = row.querySelector('.manual-name');
                const totalInput = row.querySelector('.manual-total');
                const projInput = row.querySelector('.manual-projection');

                
                entry = {
                    key: group.key,
                    id: null,
                    name: nameInput ? nameInput.value : 'N/A',
                    total: totalInput ? parseFloat(totalInput.value) || 0 : 0,
                    projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                    code: ""
                };
            }
        } else if (group.projection_manual) {
            // For purely projection groups: Save all rows (to capture their projection input)
            const projInput = row.querySelector('.manual-projection');
            
            entry = {
                key: group.key,
                id: parseInt(row.getAttribute('data-account-id')) || null,
                name: row.getAttribute('data-account-name') || '',
                total: parseFloat(row.getAttribute('data-total')) || 0,
                projection: projInput ? parseFloat(projInput.value) || 0 : 0,
                code: row.getAttribute('data-account-code') || ''
            };
        }

        // If a valid entry was created, check for the cash_account select dropdown
        if (entry) {
            const cashSelect = row.querySelector('.cash-select');
            if (cashSelect) {
                const selectedVal = cashSelect.value;
                if (selectedVal !== "null" && selectedVal !== null && selectedVal !== "") {
                    entry.cash_account = parseInt(selectedVal);
                } else {
                    entry.cash_account = null; // Stays null if "Sin Cuenta" is selected
                }
            }
            entriesToSave.push(entry);
        }
    });

    console.log("--- Data to Save ---");
    console.log(entriesToSave);

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
        showAlert("Archivo guardado","Se han actualizado correctamente los datos","","success")
        initRequest();

    })
    .catch(err => {
        console.error(err);
                showAlert("Ha ocurrido un error", "No se han actualizado correctamente los datos, intente de nuevo", "", "danger")

    });
    // console.log(JSON.stringify(entriesToSave, null, 2));
    // alert(`${entriesToSave.length} entries have been prepared for saving. (Check Console)`);
}

// --- 6. Event Listeners & Helpers ---
document.addEventListener('DOMContentLoaded', function() {
    $('#month-filter, #year-filter').on('change', initRequest);
    document.getElementById("refresh").addEventListener("click", initRequest);
    document.getElementById("save").addEventListener("click", saveData);
});


window.addManual = addManual;
window.deleteManual = deleteManual;
