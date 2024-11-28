// Utilities
function showLoading() {
    const loadingElement = document.getElementById('loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
}

function hideLoading() {
    const loadingElement = document.getElementById('loading');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
}

// Error handling
function showMessage(message, isError = true) {
    const contentElement = document.getElementById('content');
    if (contentElement) {
        const messageDiv = document.createElement('div');
        messageDiv.className = isError ? 'error-message' : 'success-message';
        messageDiv.textContent = message;

        // Remove any existing messages
        const existingMessages = document.querySelectorAll('.error-message, .success-message');
        existingMessages.forEach(msg => msg.remove());

        contentElement.prepend(messageDiv);
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

// Update active navigation item
function updateActiveNavigation(section) {
    document.querySelectorAll('nav a').forEach(link => {
        link.classList.remove('active');
        if (section === 'addAnimal' && link.id === 'link-Προσθήκη') {
            link.classList.add('active');
        } else if (link.textContent === section) {
            link.classList.add('active');
        }
    });
}

// Pagination function
function createPagination(paginationData, currentSection) {
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination';

    // Previous page button
    if (paginationData.currentPage > 1) {
        const prevButton = document.createElement('a');
        prevButton.href = '#';
        prevButton.textContent = '«';
        prevButton.onclick = (e) => {
            e.preventDefault();
            loadSection(currentSection, paginationData.currentPage - 1);
        };
        paginationContainer.appendChild(prevButton);
    }

    // Page numbers
    let startPage = Math.max(1, paginationData.currentPage - 2);
    let endPage = Math.min(paginationData.totalPages, paginationData.currentPage + 2);

    if (startPage > 1) {
        const firstPage = document.createElement('a');
        firstPage.href = '#';
        firstPage.textContent = '1';
        firstPage.onclick = (e) => {
            e.preventDefault();
            loadSection(currentSection, 1);
        };
        paginationContainer.appendChild(firstPage);

        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationContainer.appendChild(dots);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.textContent = i;
        if (i === paginationData.currentPage) {
            pageLink.className = 'active';
        }
        pageLink.onclick = (e) => {
            e.preventDefault();
            loadSection(currentSection, i);
        };
        paginationContainer.appendChild(pageLink);
    }

    if (endPage < paginationData.totalPages) {
        if (endPage < paginationData.totalPages - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationContainer.appendChild(dots);
        }

        const lastPage = document.createElement('a');
        lastPage.href = '#';
        lastPage.textContent = paginationData.totalPages;
        lastPage.onclick = (e) => {
            e.preventDefault();
            loadSection(currentSection, paginationData.totalPages);
        };
        paginationContainer.appendChild(lastPage);
    }

    // Next page button
    if (paginationData.currentPage < paginationData.totalPages) {
        const nextButton = document.createElement('a');
        nextButton.href = '#';
        nextButton.textContent = '»';
        nextButton.onclick = (e) => {
            e.preventDefault();
            loadSection(currentSection, paginationData.currentPage + 1);
        };
        paginationContainer.appendChild(nextButton);
    }

    return paginationContainer;
}

// Load species for dropdown
async function loadSpecies() {
    const speciesSelect = document.getElementById('species');
    if (!speciesSelect) return;

    try {
        const response = await fetch('get_species.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Clear existing options
        speciesSelect.innerHTML = '<option value="">Επιλέξτε είδος</option>';

        // Add new options
        if (Array.isArray(data)) {
            data.forEach(specie => {
                const option = document.createElement('option');
                option.value = specie.Onoma;
                option.textContent = specie.Onoma;
                speciesSelect.appendChild(option);
            });
        }
    } catch (error) {
        showMessage('Σφάλμα φόρτωσης ειδών: ' + error.message);
    }
}

// Show add animal form
function showAddAnimalForm() {
    // Update active navigation
    updateActiveNavigation('addAnimal');

    // Hide main content
    const contentElement = document.getElementById('content');
    if (contentElement) {
        contentElement.innerHTML = '';
    }

    // Show form container
    const formContainer = document.getElementById('addAnimalFormContainer');
    if (formContainer) {
        formContainer.style.display = 'block';

        // Load species if not already loaded
        const speciesSelect = document.getElementById('species');
        if (speciesSelect) {
            loadSpecies();
        }
    }
}

// Submit new animal
async function submitNewAnimal(event) {
    event.preventDefault();

    try {
        showLoading();

        const formData = new FormData(event.target);
        const response = await fetch('add_animal.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'error') {
            throw new Error(result.message);
        }

        // Show success message
        showMessage(result.message, false);

        // Clear form
        event.target.reset();

        // Return to animals list after 2 seconds
        setTimeout(() => {
            loadSection('Ζώα');
        }, 2000);

    } catch (error) {
        showMessage(error.message);
    } finally {
        hideLoading();
    }
}

// Load section content
async function loadSection(section, page = 1) {
    // Update active navigation
    updateActiveNavigation(section);

    // Hide add animal form
    const formContainer = document.getElementById('addAnimalFormContainer');
    if (formContainer) {
        formContainer.style.display = 'none';
    }

    const contentElement = document.getElementById('content');
    if (!contentElement) return;

    try {
        showLoading();

        const response = await fetch(`index.php?section=${encodeURIComponent(section)}&page=${page}`);
        if (!response.ok) {
            throw new Error('Σφάλμα δικτύου');
        }

        const data = await response.json();
        if (data.status === 'error') {
            throw new Error(data.message);
        }

        contentElement.innerHTML = ''; // Clear previous content

        // Create table if we have data
        if (data.data && data.data.length > 0) {
            const table = document.createElement('table');

            // Headers
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            Object.keys(data.data[0]).forEach(key => {
                const th = document.createElement('th');
                th.textContent = key;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Body
            const tbody = document.createElement('tbody');
            data.data.forEach(row => {
                const tr = document.createElement('tr');
                Object.values(row).forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value !== null ? value : '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            // Στη συνάρτηση loadSection στο script.js, πριν το contentElement.appendChild(table):

            if (data.pagination) {
                const infoDiv = document.createElement('div');
                infoDiv.className = 'pagination-info';
                const start = (data.pagination.currentPage - 1) * data.pagination.itemsPerPage + 1;
                const end = Math.min(start + data.pagination.itemsPerPage - 1, data.pagination.totalItems);
                infoDiv.textContent = `Εμφάνιση ${start}-${end} από ${data.pagination.totalItems} ${section}`;
                contentElement.appendChild(infoDiv);
            }

            contentElement.appendChild(table);

            // Add pagination if needed
            if (data.pagination && data.pagination.totalPages > 1) {
                contentElement.appendChild(createPagination(data.pagination, section));
            }
        } else {
            contentElement.innerHTML = '<p>Δεν βρέθηκαν δεδομένα</p>';
        }

    } catch (error) {
        showMessage(error.message);
    } finally {
        hideLoading();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadSection('Ζώα');
});

// Generic form handling functions
function showForm(formType, entityType, data = null) {
    const contentElement = document.getElementById('content');
    if (!contentElement) return;

    // Hide main content and other forms
    contentElement.innerHTML = '';
    const existingForms = document.querySelectorAll('.entity-form-container');
    existingForms.forEach(form => form.style.display = 'none');

    // Create form container
    const formContainer = document.createElement('div');
    formContainer.className = 'entity-form-container';
    formContainer.id = `${entityType}FormContainer`;

    // Create form title
    const formTitle = document.createElement('h2');
    formTitle.textContent = `${formType} ${entityType}`;
    formContainer.appendChild(formTitle);

    // Create form element
    const form = document.createElement('form');
    form.id = `${entityType}Form`;
    form.className = 'entity-form';
    form.onsubmit = (e) => handleFormSubmit(e, entityType, formType, data?.id);

    // Add form fields based on entity type
    const fields = getFormFields(entityType);
    fields.forEach(field => {
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group';

        const label = document.createElement('label');
        label.htmlFor = field.name;
        label.textContent = field.label;

        let input;
        if (field.type === 'select') {
            input = document.createElement('select');
            loadDropdownOptions(input, field.source);
        } else {
            input = document.createElement('input');
            input.type = field.type;
        }

        input.id = field.name;
        input.name = field.name;
        input.required = field.required !== false;

        // If editing, populate with existing data
        if (data && data[field.name]) {
            input.value = data[field.name];
        }

        formGroup.appendChild(label);
        formGroup.appendChild(input);
        form.appendChild(formGroup);
    });

    // Add submit button
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = formType;
    form.appendChild(submitButton);

    // Add cancel button
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Ακύρωση';
    cancelButton.onclick = () => loadSection(entityType);
    cancelButton.className = 'cancel-button';
    form.appendChild(cancelButton);

    formContainer.appendChild(form);
    contentElement.appendChild(formContainer);
}

// Get form fields configuration based on entity type
function getFormFields(entityType) {
    const fieldConfigs = {
        'Ζώα': [
            { name: 'kodikos', label: 'Κωδικός', type: 'text' },
            { name: 'onoma', label: 'Όνομα', type: 'text' },
            { name: 'etos_genesis', label: 'Έτος Γέννησης', type: 'number' },
            { name: 'onoma_eidous', label: 'Είδος', type: 'select', source: 'get_species.php' }
        ],
        'Είδη': [
            { name: 'onoma', label: 'Όνομα', type: 'text' },
            { name: 'katigoria', label: 'Κατηγορία', type: 'text' },
            { name: 'perigrafi', label: 'Περιγραφή', type: 'text' }
        ],
        'Εκδηλώσεις': [
            { name: 'titlos', label: 'Τίτλος', type: 'text' },
            { name: 'hmerominia', label: 'Ημερομηνία', type: 'date' },
            { name: 'perigrafi', label: 'Περιγραφή', type: 'text' }
        ],
        // Add configurations for other entities...
    };

    return fieldConfigs[entityType] || [];
}

// Handle form submission
async function handleFormSubmit(event, entityType, formType, id = null) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const action = formType.toLowerCase();
        const response = await fetch(`${action}_${entityType.toLowerCase()}.php`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage(result.message, false);
        setTimeout(() => loadSection(entityType), 2000);
    } catch (error) {
        showMessage(error.message);
    } finally {
        hideLoading();
    }
}

// Load dropdown options
async function loadDropdownOptions(selectElement, sourceUrl) {
    try {
        const response = await fetch(sourceUrl);
        const data = await response.json();

        selectElement.innerHTML = '<option value="">Επιλέξτε...</option>';

        if (Array.isArray(data)) {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.Onoma || item.ID || item.value;
                option.textContent = item.Onoma || item.name || item.label;
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        showMessage(`Σφάλμα φόρτωσης επιλογών: ${error.message}`);
    }
}

// Add action buttons to table rows
function addActionButtons(table, entityType) {
    const headerRow = table.querySelector('thead tr');
    const actionHeader = document.createElement('th');
    actionHeader.textContent = 'Ενέργειες';
    headerRow.appendChild(actionHeader);

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const actionCell = document.createElement('td');
        actionCell.className = 'action-buttons';

        // Edit button
        const editButton = document.createElement('button');
        editButton.className = 'edit-button';
        editButton.textContent = 'Επεξεργασία';
        editButton.onclick = () => showForm('Επεξεργασία', entityType, getRowData(row));

        // Delete button
        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.textContent = 'Διαγραφή';
        deleteButton.onclick = () => handleDelete(entityType, getRowData(row));

        actionCell.appendChild(editButton);
        actionCell.appendChild(deleteButton);
        row.appendChild(actionCell);
    });
}

// Get row data as object
function getRowData(row) {
    const cells = row.cells;
    const headers = row.parentElement.parentElement.querySelector('thead tr').cells;
    const data = {};

    for (let i = 0; i < cells.length - 1; i++) {
        const header = headers[i].textContent;
        data[header] = cells[i].textContent;
    }

    return data;
}

// Handle delete operation
async function handleDelete(entityType, rowData) {
    if (!confirm(`Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το στοιχείο;`)) {
        return;
    }

    try {
        showLoading();
        const response = await fetch(`delete_${entityType.toLowerCase()}.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(rowData)
        });

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage(result.message, false);
        loadSection(entityType);
    } catch (error) {
        showMessage(error.message);
    } finally {
        hideLoading();
    }
}