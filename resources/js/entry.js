(() => {
    'use strict'

    // 1. Global variables to store our data and Choices instances
    let allAccounts = [];
    let debitChoices, creditChoices;
    const typeSelect = document.getElementById('entry_type');
    const debitSelect = document.getElementById('debit_account_id');
    const creditSelect = document.getElementById('credit_account_id');

    // Initialize Choices instances immediately but keep them disabled
    debitChoices = new Choices(debitSelect, { searchPlaceholderValue: "Buscar cuenta...", removeItemButton: false, shouldSort: false });
    creditChoices = new Choices(creditSelect, { searchPlaceholderValue: "Buscar cuenta...", removeItemButton: false, shouldSort: false });
    
    debitChoices.disable();
    creditChoices.disable();

    // Initialize Form Validation & Submit
    const forms = document.querySelectorAll('#entry-form.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', async event => {
            const submitButton = form.querySelector('button[type="submit"]');
            // Assuming setButtonLoading is defined elsewhere
            if(typeof setButtonLoading === 'function') setButtonLoading(submitButton, true);
            
            event.preventDefault(); 

            if (!form.checkValidity()) {
                event.stopPropagation();
                if(typeof setButtonLoading === 'function') await setButtonLoading(submitButton, false);
            } else {
                console.log(form);
                await addEntry(form);
                if(typeof setButtonLoading === 'function') await setButtonLoading(submitButton, false);
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 2. Fetch data ONCE and store it
    function initRequest() {
        const token = localStorage.getItem('finance_auth_token');
        if (!token) return;

        fetch(`${api_url}accounts/entries`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            // Store the data globally so we can filter it later
            allAccounts = data.data; 
        })
        .catch(error => console.error(error));
    }

    // 3. Handle Workflow Changes (entry_type select)
    typeSelect.addEventListener('change', (e) => {
        const type = e.target.value;
        
        // Reset both selects when type changes
        debitChoices.clearStore();
        creditChoices.clearStore();
        creditChoices.disable(); // Always start credit as disabled on type change

        if (['income', 'expense', 'transfer', 'asset_acquisition'].includes(type)) {
            // NORMAL WORKFLOW
            populateChoices(debitChoices, allAccounts);
            populateChoices(creditChoices, allAccounts); // Populate it, but keep it disabled
            debitChoices.enable();
            
        } else if (type === 'opening_balance') {
            // OPENING BALANCE WORKFLOW (Nature == debit)
            const filteredAccounts = allAccounts.filter(acc => acc.nature === 'debit');
            populateChoices(debitChoices, filteredAccounts);
            debitChoices.enable();
            
        } else if (type === 'opening_balance_credit') {
            // OPENING BALANCE CREDIT WORKFLOW (Nature == credit)
            const filteredAccounts = allAccounts.filter(acc => acc.nature === 'credit');
            populateChoices(debitChoices, filteredAccounts);
            debitChoices.enable();
        }
    });

    // 4. Handle cross-disabling for normal workflow
    debitSelect.addEventListener('change', (e) => {
        const currentType = typeSelect.value;
        
        // Only apply this cross-disabling logic to the normal workflows
        if (['income', 'expense', 'transfer','asset_acquisition'].includes(currentType)) {
            const selectedDebitId = e.detail.value; // Get selected value from Choices.js event
            
            // Re-populate the credit choices, but mark the selected debit account as disabled
            populateChoices(creditChoices, allAccounts, selectedDebitId);
            creditChoices.enable(); 
        }else{
            const filteredAccounts = allAccounts
    .filter(acc => acc.code?.startsWith('300.'))
    .reverse();
            populateChoices(creditChoices, filteredAccounts);
            creditChoices.enable();
        }
    });

    // 5. Helper function to feed data into Choices instances
    function populateChoices(choicesInstance, accountsList, disabledId = null) {
        const formattedChoices = accountsList.map(m => ({
            value: m.id,
            label: `${m.code} - ${m.name}`,
            disabled: String(m.id) === String(disabledId), // Disable if it matches the other select
            customProperties: {
                code: m.code,
                type: m.type,
                parent_id: m.id,
                root: m.name,
                nature: m.nature
            }
        }));
        
        // The 'true' parameter tells Choices.js to replace existing choices entirely
        choicesInstance.setChoices(formattedChoices, 'value', 'label', true); 
    }

    // 6. Submit function
    async function addEntry(form) {
        const token = localStorage.getItem('finance_auth_token');
        if (!token) return;

        // Note: For opening balances, credit_account_id is disabled. 
        // A disabled select will not be submitted, so we manually check for its value and fallback to null.
        const payload = {
            "entry_date": form.entry_date.value,
            "entry_type": form.entry_type.value,
            "amount": form.amount.value,
            "debit_account_id": form.debit_account_id.value,
            "credit_account_id": form.credit_account_id.value || null, // Handles disabled state
            "description": form.description.value,
            // "applies_se": form.applies_se.value,
            // "applies_fe": form.applies_fe.value
        };

        try {
            const response = await fetch(`${api_url}entries`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                if(typeof handleApiError === 'function') handleApiError(response.status, data);
                return;
            }

            if(typeof showAlert === 'function') showAlert("Entrada actualizada","Se han registrado correctamente los datos","","success");
            
            // Optional: reset form and lock selects after successful submission
            form.reset();
            form.classList.remove('was-validated');
            debitChoices.clearStore();
            creditChoices.clearStore();
            debitChoices.disable();
            creditChoices.disable();

            return data;
        } catch (error) {
            console.error(error);
            if(typeof showAlert === 'function') showAlert("Ha ocurrido un error","No se han registrado correctamente los datos, intente de nuevo","","danger");
            return error;
        }
    }

    // Trigger initial load
    initRequest();

})();
