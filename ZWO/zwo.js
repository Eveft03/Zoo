// zwo/zwo-operations.js

// Form field definitions
const zwoFields = [
    { name: 'kodikos', label: 'Κωδικός', required: true, pattern: '^Z\\d{6}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'etos_genesis', label: 'Έτος Γέννησης', required: true, type: 'number', min: 1900, max: new Date().getFullYear() },
    { name: 'onoma_eidous', label: 'Είδος', required: true, type: 'select', dataSource: 'get_species.php' }
];

// Create ZWO form
function createZwoForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleZwoSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Ζώου`;
    form.appendChild(title);

    zwoFields.forEach(field => {
        const formGroup = createFormField(field, data?.[field.name]);
        form.appendChild(formGroup);
    });

    // Add form buttons
    const buttonsDiv = document.createElement('div');
    buttonsDiv.className = 'form-buttons';

    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = formType;
    buttonsDiv.appendChild(submitButton);

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Ακύρωση';
    cancelButton.className = 'cancel-button';
    cancelButton.onclick = () => loadSection('Ζώα');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

// Handle ZWO form submission
async function handleZwoSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'zwo/add_zwo.php' : 'zwo/update_zwo.php';

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Σφάλμα απόκρισης: ${errorText}`);
        }

        const result = await response.json();
        if (result.status === 'error') throw new Error(result.message);

        showMessage(result.message, false);
        loadSection('Ζώα');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

// Handle ZWO deletion
async function handleZwoDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το ζώο;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('zwo/delete_zwo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'error') throw new Error(result.message);

        showMessage(result.message, false);
        await loadSection('Ζώα');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createZwoForm,
    handleZwoSubmit,
    handleZwoDelete,
    zwoFields
};