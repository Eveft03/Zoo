// eisitirio/eisitirio-operations.js

const eisitirioFields = [
    { name: 'kodikos', label: 'Κωδικός', required: true, type: 'text' },
    { name: 'hmerominia_ekdoshs', label: 'Ημερομηνία Έκδοσης', required: true, type: 'date' },
    { name: 'timi', label: 'Τιμή', required: true, type: 'number', min: 0 },
    { name: 'idTamia', label: 'ID Ταμία', required: true, pattern: '^TM\\d{3}$', type: 'text' },
    { name: 'email', label: 'Email Επισκέπτη', required: true, type: 'email' },
    { name: 'katigoria', label: 'Κατηγορία', required: true, type: 'select', options: ['Με εκδήλωση', 'Χωρίς εκδήλωση'] }
];

function createEisitirioForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleEisitirioSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Εισιτηρίου`;
    form.appendChild(title);

    eisitirioFields.forEach(field => {
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
    cancelButton.onclick = () => loadSection('Εισιτήρια');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleEisitirioSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'eisitirio/add_eisitirio.php' : 'eisitirio/update_eisitirio.php';

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
        loadSection('Εισιτήρια');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handleEisitirioDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το εισιτήριο;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('eisitirio/delete_eisitirio.php', {
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
        await loadSection('Εισιτήρια');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createEisitirioForm,
    handleEisitirioSubmit,
    handleEisitirioDelete,
    eisitirioFields
};