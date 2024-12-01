// frontistis/frontistis-operations.js

const frontistisFields = [
    { name: 'id', label: 'ID', required: true, pattern: '^FR\\d{3}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
    { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
    { name: 'misthos', label: 'Μισθός', required: true, type: 'number', min: 0 },
    { name: 'zwa', label: 'Ζώα Φροντίδας', required: false, type: 'multiselect', dataSource: 'get_animals.php' }
];

function createFrontistisForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleFrontistisSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Φροντιστή`;
    form.appendChild(title);

    frontistisFields.forEach(field => {
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
    cancelButton.onclick = () => loadSection('Φροντιστές');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleFrontistisSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'frontistis/add_frontistis.php' : 'frontistis/update_frontistis.php';

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
        loadSection('Φροντιστές');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handleFrontistisDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον φροντιστή;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('frontistis/delete_frontistis.php', {
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
        await loadSection('Φροντιστές');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createFrontistisForm,
    handleFrontistisSubmit,
    handleFrontistisDelete,
    frontistisFields
};