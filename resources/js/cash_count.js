function initRequest() {
    const token = localStorage.getItem('finance_auth_token');
    let data_url = document.getElementById("data_url").value;
    const url = new URL(data_url);
    const today = new Date();
    const month = document.getElementById('month-filter')?.value ?? (today.getMonth() + 1); // months are 0-based
    const year = document.getElementById('year-filter')?.value ?? today.getFullYear();
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
            initAuxModal(data.data);
        })
        .catch((error) => {
            console.error("Failed to fetch initial data:", error);
        });
}

function formatMoney(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

function parseNumber(value) {
    return parseFloat(value.replace(/,/g, '')) || 0;
}

function calculate() {
    let saldo = 0;
    document.querySelectorAll('#cash-table tr').forEach(row => {
        const input = row.querySelector('.qty');
        const totalCell = row.querySelector('.row-total');
        const qty = parseFloat(input.value) || 0;
        const denomination = parseFloat(input.dataset.value);
        const total = qty * denomination;
        totalCell.innerText = formatMoney(total);
        saldo += total;
    });
    document.getElementById('saldo').innerText = formatMoney(saldo);
    const aux = parseNumber(document.getElementById('aux').value);
    const diff = saldo - aux;
    const diffElement = document.getElementById('difference');
    document.getElementById('difference_amount').value = diff;
    diffElement.innerText = formatMoney(diff);

    diffElement.classList.remove('text-success', 'text-danger');
    if (diff === 0) {
        diffElement.classList.add('text-success');
    } else {
        diffElement.classList.add('text-danger');
    }
}

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty') || e.target.id === 'aux') {
        calculate();
    }
});
let auxChoices = null;
let auxData = [];

function initAuxModal(data) {
    auxData = data;

    const select = document.getElementById('aux-select');

    if (auxChoices) {
        auxChoices.destroy();
    }

    select.innerHTML = data.map(item => `
        <option value="${item.account_id}">
            ${item.account_code} - ${item.account_name} ($${parseFloat(item.total).toLocaleString()})
        </option>
    `).join('');

    auxChoices = new Choices(select, {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false
    });

    // 🔥 AUTO SELECT DEFAULT ACCOUNT
    const defaultAccount = data.find(item => item.account_code.startsWith('100.1.'));

    if (defaultAccount) {
        console.log("Default account found:", defaultAccount);
        // Select in Choices
        auxChoices.setChoiceByValue(defaultAccount.account_id.toString());

        // Set value in S. en Aux
        const auxInput = document.getElementById('aux');
        auxInput.value = parseFloat(defaultAccount.total).toLocaleString();

        // Recalculate
        calculate();
    }
}
document.getElementById('addCount').addEventListener('click', async () => {
    let modal = await confirmModal({
        title: `¿Estás seguro de crear registro a esta cuenta?`,
        text: 'Estás a punto de agregar un registro a la cuenta 500.1 (Gastos No Identificados)',
        mode: 'confirm',
        confirmText: 'Crear registro'
    });
    if (modal) {
        const token = localStorage.getItem('finance_auth_token');
        if (!token) return;

        const payload = {
            "debit_account_id": document.getElementById("aux-select").value,
            "entry_month": document.getElementById("month-filter").value,
            "entry_year": document.getElementById("year-filter").value,
            "amount": document.getElementById("difference_amount").value,
        };

        try {
            let submitButton = document.getElementById("addCount");
            setButtonLoading(submitButton, true);
            const response = await fetch(`${api_url}entries/cash_count`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (response.ok) {
                setButtonLoading(submitButton, false);
                if (data.success) {
                    initRequest();
                    showAlert("Movimiento agregado", "Se ha agregado este movimiento", "", "success");
                } else {
                    showAlert("Ha ocurrido un error", data.message, "", "danger");
                }
            } else {
                setButtonLoading(submitButton, false);
                showAlert("Ha ocurrido un error", data.message ?? "Error en la petición", "", "danger");
            }
        } catch (error) {
            setButtonLoading(submitButton, false);
            console.log(error);
            showAlert("Ha ocurrido un error", "No se ha agregado el movimiento, intente de nuevo", "", "danger");
            return error;
        }
    }
});

document.getElementById('confirm-aux').addEventListener('click', () => {
    const selectedId = document.getElementById('aux-select').value;
    const selected = auxData.find(item => item.account_id == selectedId);
    if (!selected) return;
    console.log("Selected account:", selected);
    const auxInput = document.getElementById('aux');
    auxInput.value = parseFloat(selected.total);
    calculate();
    const modal = bootstrap.Modal.getInstance(document.getElementById('auxModal'));
    modal.hide();
});

document.addEventListener('DOMContentLoaded', function () {
    initRequest();
    $('#month-filter, #year-filter').on('change', initRequest);
});
calculate();
