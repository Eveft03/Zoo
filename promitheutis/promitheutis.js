
import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';
import { createFormField } from '../ValidationFunctions.js';

const promitheutisFields = [
    { name: 'afm', label: 'ΑΦΜ', required: true, pattern: '^\\d{9}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'thlefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' }
];

function createpromitheutisForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handlepromitheutisSubmit(e, formType);

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

async function handlepromitheutisSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = `./promitheutis/${formType === 'Προσθήκη' ? 'add' : 'update'}_promitheuti.php`;

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Λάθος τύπος απάντησης από τον server');
        }

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage(result.message, false);
        loadSection('Προμηθευτές');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}
async function handlepromitheutisDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον προμηθευτή;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('promitheutis/delete_promitheuti.php', {
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
    createpromitheutisForm,
    handlepromitheutisSubmit,
    handlepromitheutisDelete,
    promitheutisFields
};