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

// Add this to all form submit handlers
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

export { validators, validateForm, handleSubmit };