// promitheutis/promitheutis-operations.js
import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';
import { createFormField } from '../ValidationFunctions.js';

const promitheutisFields = [
    { name: 'afm', label: 'ΑΦΜ', required: true, pattern: '^\\d{9}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
    { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
    { name: 'dieuthinsi', label: 'Διεύθυνση', required: true, type: 'text' },
    { name: 'trofima', label: 'Προϊόντα', required: false, type: 'multiselect', dataSource: 'get_trofima.php' }
];

function createPromitheutisForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handlePromitheutisSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Προμηθευτή`;
    form.appendChild(title);

    promitheutisFields.forEach(field => {
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
    cancelButton.onclick = () => loadSection('Προμηθευτές');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handlePromitheutisSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'promitheutis/add_promitheutis.php' : 'promitheutis/update_promitheutis.php';

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
        loadSection('Προμηθευτές');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handlePromitheutisDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον προμηθευτή;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('promitheutis/delete_promitheutis.php', {
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
        await loadSection('Προμηθευτές');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createPromitheutisForm,
    handlePromitheutisSubmit,
    handlePromitheutisDelete,
    promitheutisFields
};