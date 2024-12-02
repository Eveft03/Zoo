
import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';
import { createFormField } from '../ValidationFunctions.js';

const promiueftisFields = [
    { name: 'afm', label: 'ΑΦΜ', required: true, pattern: '^\\d{9}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
    { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
    { name: 'dieuthinsi', label: 'Διεύθυνση', required: true, type: 'text' },
    { name: 'trofima', label: 'Προϊόντα', required: false, type: 'multiselect', dataSource: 'get_trofima.php' }
];

function createpromiueftisForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handlepromiueftisSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Προμηθευτή`;
    form.appendChild(title);

    promiueftisFields.forEach(field => {
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

async function handlepromiueftisSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'promiueftis/add_promiueftis.php' : 'promiueftis/update_promiueftis.php';

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

async function handlepromiueftisDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον προμηθευτή;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('promiueftis/delete_promiueftis.php', {
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
    createpromiueftisForm,
    handlepromiueftisSubmit,
    handlepromiueftisDelete,
    promiueftisFields
};