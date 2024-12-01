// ekdilosi/ekdilosi-operations.js

const ekdilosiFields = [
    { name: 'titlos', label: 'Τίτλος', required: true, type: 'text' },
    { name: 'hmerominia', label: 'Ημερομηνία', required: true, type: 'date' },
    { name: 'ora', label: 'Ώρα', required: true, type: 'time' },
    { name: 'xwros', label: 'Χώρος', required: true, type: 'text' },
    { name: 'zwa', label: 'Συμμετέχοντα Ζώα', required: false, type: 'multiselect', dataSource: 'get_animals.php' }
];

function createEkdilosiForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleEkdilosiSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Εκδήλωσης`;
    form.appendChild(title);

    if (formType === 'Επεξεργασία') {
        // Add hidden fields for old values when editing
        const oldTitlos = document.createElement('input');
        oldTitlos.type = 'hidden';
        oldTitlos.name = 'old_titlos';
        oldTitlos.value = data?.titlos || '';
        form.appendChild(oldTitlos);

        const oldHmerominia = document.createElement('input');
        oldHmerominia.type = 'hidden';
        oldHmerominia.name = 'old_hmerominia';
        oldHmerominia.value = data?.hmerominia || '';
        form.appendChild(oldHmerominia);
    }

    ekdilosiFields.forEach(field => {
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
    cancelButton.onclick = () => loadSection('Εκδηλώσεις');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleEkdilosiSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'ekdilosi/add_ekdilosi.php' : 'ekdilosi/update_ekdilosi.php';

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
        loadSection('Εκδηλώσεις');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handleEkdilosiDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εκδήλωση;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('ekdilosi/delete_ekdilosi.php', {
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
        await loadSection('Εκδηλώσεις');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createEkdilosiForm,
    handleEkdilosiSubmit,
    handleEkdilosiDelete,
    ekdilosiFields
};