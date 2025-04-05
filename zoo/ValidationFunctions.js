// Common validation functions
const validators = {
    required: (value) => value && value.trim().length > 0,
    email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
    phone: (value) => /^\d{10}$/.test(value),
    numeric: (value) => !isNaN(value) && value > 0,
    date: (value) => !isNaN(Date.parse(value)),
    time: (value) => /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(value),
    animalCode: (value) => /^Z\d{6}$/.test(value),
    caretakerId: (value) => /^FR\d{3}$/.test(value),
    cashierId: (value) => /^TM\d{3}$/.test(value),
    ticketCode: (value) => /^\d{5}$/.test(value),
    zwoCode: (value) => /^Z\d{6}$/.test(value),
    speciesName: (value) => /^[Α-Ωα-ω\s]{2,50}$/.test(value),
    year: (value) => {
        const year = parseInt(value);
        return year >= 1900 && year <= new Date().getFullYear();

    }
};

    function validateForm(formData, fields) {
        const errors = [];

fields.forEach(field => {
    const value = formData.get(field.name);

    if (field.required && !validators.required(value)) {
        errors.push(`Το πεδίο ${field.label} είναι υποχρεωτικό`);
        return;
    }

    if (value) {
        switch (field.type) {
            case 'email':
                if (!validators.email(value)) {
                    errors.push('Μη έγκυρη διεύθυνση email');
                }
                break;
            case 'tel':
                if (!validators.phone(value)) {
                    errors.push('Μη έγκυρος αριθμός τηλεφώνου');
                }
                break;
            case 'number':
                if (!validators.numeric(value)) {
                    errors.push(`Το πεδίο ${field.label} πρέπει να είναι θετικός αριθμός`);
                }
                break;
            case 'date':
                if (!validators.date(value)) {
                    errors.push('Μη έγκυρη ημερομηνία');
                }
                break;
            case 'time':
                if (!validators.time(value)) {
                    errors.push('Μη έγκυρη ώρα');
                }
                break;
        }

        if (field.pattern) {
            const regex = new RegExp(field.pattern);
            if (!regex.test(value)) {
                errors.push(`Μη έγκυρη μορφή για το πεδίο ${field.label}`);
            }
        }
    }
});

return errors;
}

async function handleSubmit(event, formType, endpoint, fields) {
    event.preventDefault();
    const formData = new FormData(event.target);

    const errors = validateForm(formData, fields);
    if (errors.length > 0) {
        showMessage(errors.join('\n'), true);
        return;
    }

    try {
        showLoading();
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error: ${response.status}`);
        }

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage(result.message, false);
        loadSection(formType);
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

function createFormField(field, value = null) {
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

        if (field.options) {
            field.options.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option;
                opt.textContent = option;
                if (value === option) opt.selected = true;
                select.appendChild(opt);
            });
        }

        formGroup.appendChild(select);
    } else if (field.type === 'textarea') {
        const textarea = document.createElement('textarea');
        textarea.name = field.name;
        textarea.id = field.name;
        textarea.required = field.required;
        if (value) textarea.value = value;
        formGroup.appendChild(textarea);
    } else {
        const input = document.createElement('input');
        input.type = field.type;
        input.name = field.name;
        input.id = field.name;
        input.required = field.required;
        input.placeholder = ' ';
        if (field.pattern) input.pattern = field.pattern;
        if (field.min !== undefined) input.min = field.min;
        if (field.max !== undefined) input.max = field.max;
        if (value) input.value = value;
        formGroup.appendChild(input);
    }

    return formGroup;
}

function setupFormValidation(form) {
    const inputs = form.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        if (input.type !== 'select') {
            input.placeholder = ' ';
        }

        input.addEventListener('input', () => validateInput(input));
        input.addEventListener('blur', () => validateInput(input));
    });
}

function validateInput(input) {
    if (input.validity.valid) {
        input.style.borderColor = '#3498db';
        input.style.boxShadow = '0 0 0 2px rgba(52, 152, 219, 0.2)';

        if (input.pattern && new RegExp(input.pattern).test(input.value)) {
            input.style.borderColor = '#2ecc71';
            input.style.boxShadow = '0 0 0 2px rgba(46, 204, 113, 0.2)';
        }
    } else {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 0 2px rgba(231, 76, 60, 0.2)';
    }
}

export { validators, validateForm, handleSubmit, createFormField, setupFormValidation };