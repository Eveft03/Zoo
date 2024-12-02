import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';
import { createFormField } from '../ValidationFunctions.js';


const episkeptisFields = [
    { 
        name: 'email', 
        label: 'Email *', 
        required: true, 
        type: 'email',
        pattern: '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}',
        title: 'Εισάγετε έγκυρη διεύθυνση email'
    },
    { 
        name: 'onoma', 
        label: 'Όνομα *', 
        required: true, 
        type: 'text',
        minLength: 2,
        maxLength: 50
    },
    { 
        name: 'eponymo', 
        label: 'Επώνυμο *', 
        required: true, 
        type: 'text',
        minLength: 2,
        maxLength: 50
    },
    { 
        name: 'tilefono', 
        label: 'Τηλέφωνο *', 
        required: true, 
        pattern: '^\\d{10}$', 
        type: 'tel',
        title: 'Εισάγετε 10ψήφιο αριθμό τηλεφώνου'
    }
];

function createepiskeptisForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleepiskeptisSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Επισκέπτη`;
    form.appendChild(title);

    // Προσθήκη υπόμνησης για υποχρεωτικά πεδία
    const required = document.createElement('p');
    required.className = 'required-fields-note';
    required.textContent = '* Υποχρεωτικά πεδία';
    form.appendChild(required);

    episkeptisFields.forEach(field => {
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
    cancelButton.onclick = () => {
        if (confirm('Είστε σίγουροι ότι θέλετε να ακυρώσετε την καταχώρηση;')) {
            loadSection('Επισκέπτες');
        }
    };
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleepiskeptisSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? 'episkeptis/add_episkepti.php' : 'episkeptis/update_episkepti.php';

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
        loadSection('Επισκέπτες');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

async function handleepiskeptisDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον επισκέπτη;')) {
        return;
    }

    try {
        showLoading();

        const response = await fetch('episkeptis/delete_episkepti.php', {
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
        await loadSection('Επισκέπτες');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

export {
    createepiskeptisForm,
    handleepiskeptisSubmit,
    handleepiskeptisDelete,
    episkeptisFields
};