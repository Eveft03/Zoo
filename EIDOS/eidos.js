

import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';
import { createFormField } from '../ValidationFunctions.js';

const eidosFields = [
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'katigoria', label: 'Κατηγορία', required: true, type: 'text' },
    { name: 'perigrafi', label: 'Περιγραφή', required: false, type: 'textarea' }
];

function createeidosForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleeidosSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Είδους`;
    form.appendChild(title);

    eidosFields.forEach(field => {
        const formGroup = createFormField(field, data?.[field.name]);
        form.appendChild(formGroup);
    });

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
    cancelButton.onclick = () => loadSection('Είδη');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleeidosSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'eidos/add_eidos.php' : 'eidos/update_eidos.php';

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
        loadSection('Είδη');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handleeidosDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το είδος;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('eidos/delete_eidos.php', {
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
        await loadSection('Είδη');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createeidosForm,
    handleeidosSubmit,
    handleeidosDelete,
    eidosFields
};