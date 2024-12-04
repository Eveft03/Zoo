// Import necessary functions

import { showMessage, showLoading, hideLoading, loadSection } from '../script.js';


const zwoFields = [
    { name: 'kodikos', label: 'Κωδικός', required: true, pattern: '^Z\\d{6}$', type: 'text' },
    { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
    { name: 'etos_genesis', label: 'Έτος Γέννησης', required: true, type: 'number', min: 1900, max: new Date().getFullYear() },
    { name: 'onoma_eidous', label: 'Είδος', required: true, type: 'select', dataSource: 'get_species.php' }
];

function createZwoForm(formType, data = null) {
    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleZwoSubmit(e, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} Ζώου`;
    form.appendChild(title);

    zwoFields.forEach(field => {
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group';

        const label = document.createElement('label');
        label.htmlFor = field.name;
        label.textContent = field.label;
        if (field.required) label.classList.add('required');
        formGroup.appendChild(label);

        if (field.type === 'select') {
            const select = document.createElement('select');
            select.name = field.name;
            select.id = field.name;
            select.required = field.required;
            
            // Populate select with options from dataSource
            fetch(field.dataSource)
                .then(response => response.json())
                .then(species => {
                    species.forEach(s => {
                        const option = document.createElement('option');
                        option.value = s.Onoma;
                        option.textContent = s.Onoma;
                        if (data && data[field.name] === s.Onoma) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching species:', error));

            formGroup.appendChild(select);
        } else {
            const input = document.createElement('input');
            input.type = field.type;
            input.name = field.name;
            input.id = field.name;
            input.required = field.required;
            if (field.pattern) input.pattern = field.pattern;
            if (field.min !== undefined) input.min = field.min;
            if (field.max !== undefined) input.max = field.max;
            if (data && data[field.name]) input.value = data[field.name];
            formGroup.appendChild(input);
        }

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
    cancelButton.onclick = () => loadSection('Ζώα');
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    return form;
}

async function handleZwoSubmit(event, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = `./zwo/${formType === 'Προσθήκη' ? 'add' : 'update'}_zwo.php`;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });

        const text = await response.text();
        console.log('Server response:', text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            throw new Error('Μη έγκυρη απάντηση από τον server');
        }

        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage(result.message, false);
        loadSection('Ζώα');
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}
async function handleZwoDelete(data) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το ζώο;')) {
        return;
    }

    try {
        showLoading();
        const response = await fetch('./zwo/delete_zwo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message);
        }

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