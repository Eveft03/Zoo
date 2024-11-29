// ================ Utility Functions ================

/**
 * Εμφανίζει τον loading spinner κατά τη διάρκεια των ασύγχρονων λειτουργιών
 */
function showLoading() {
    document.getElementById('loading').style.display = 'flex';
}

/**
 * Κρύβει τον loading spinner μετά την ολοκλήρωση των ασύγχρονων λειτουργιών
 */
function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

/**
 * Εμφανίζει μηνύματα επιτυχίας ή σφάλματος στον χρήστη
 * @param {string} message - Το μήνυμα που θα εμφανιστεί
 * @param {boolean} isError - Αν είναι μήνυμα σφάλματος (true) ή επιτυχίας (false)
 */
function showMessage(message, isError = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isError ? 'error-message' : 'success-message'}`;
    messageDiv.textContent = message;

    const contentElement = document.getElementById('content');
    const existingMessage = contentElement.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }

    contentElement.insertBefore(messageDiv, contentElement.firstChild);
    setTimeout(() => messageDiv.remove(), 5000);
}

// ================ Navigation Functions ================

/**
 * Ενημερώνει το active link στο navigation menu
 * @param {string} section - Η τρέχουσα ενότητα
 */
function updateActiveNavigation(section) {
    document.querySelectorAll('nav a').forEach(link => {
        link.classList.toggle('active', link.textContent === section);
    });
}

/**
 * Κύρια συνάρτηση φόρτωσης περιεχομένου ενότητας
 * @param {string} section - Η ενότητα προς φόρτωση
 * @param {number} page - Ο αριθμός σελίδας για pagination
 */
async function loadSection(section, page = 1) {
    updateActiveNavigation(section);
    const contentElement = document.getElementById('content');
    if (!contentElement) return;

    try {
        showLoading();

        const response = await fetch(`index.php?section=${encodeURIComponent(section)}&page=${page}`);
        if (!response.ok) throw new Error('Σφάλμα δικτύου');

        const data = await response.json();
        if (data.status === 'error') throw new Error(data.message);

        contentElement.innerHTML = '';

        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-container';
        contentElement.appendChild(tableContainer);

        // Προσθήκη κουμπιού για νέα εγγραφή
        const addButton = document.createElement('button');
        addButton.className = 'add-button';
        addButton.textContent = `Προσθήκη ${section}`;
        addButton.onclick = () => showForm('Προσθήκη', section);
        tableContainer.appendChild(addButton);

        if (data.data && data.data.length > 0) {
            // Εμφάνιση πληροφοριών σελιδοποίησης
            const infoDiv = document.createElement('div');
            infoDiv.className = 'pagination-info';
            const start = (data.pagination.currentPage - 1) * data.pagination.itemsPerPage + 1;
            const end = Math.min(start + data.pagination.itemsPerPage - 1, data.pagination.totalItems);
            infoDiv.textContent = `Εμφάνιση ${start}-${end} από ${data.pagination.totalItems} ${section}`;
            tableContainer.appendChild(infoDiv);

            // Δημιουργία πίνακα δεδομένων
            const table = createDataTable(data.data, section);
            tableContainer.appendChild(table);

            // Προσθήκη pagination
            if (data.pagination && data.pagination.totalPages > 1) {
                const pagination = createPagination(data.pagination, section);
                tableContainer.appendChild(pagination);
            }
        } else {
            const noDataMessage = document.createElement('p');
            noDataMessage.textContent = 'Δεν βρέθηκαν δεδομένα';
            tableContainer.appendChild(noDataMessage);
        }
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

/**
 * Δημιουργεί τον πίνακα δεδομένων με τα controls
 * @param {Array} data - Τα δεδομένα προς εμφάνιση
 * @param {string} section - Η τρέχουσα ενότητα
 * @returns {HTMLElement} - Ο πίνακας με τα δεδομένα
 */
function createDataTable(data, section) {
    const table = document.createElement('table');
    const thead = document.createElement('thead');
    const tbody = document.createElement('tbody');

    // Δημιουργία headers
    const headerRow = document.createElement('tr');
    Object.keys(data[0]).forEach(key => {
        const th = document.createElement('th');
        th.textContent = key;
        headerRow.appendChild(th);
    });

    // Προσθήκη header για τις ενέργειες
    const actionsHeader = document.createElement('th');
    actionsHeader.textContent = 'Ενέργειες';
    headerRow.appendChild(actionsHeader);

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Δημιουργία γραμμών δεδομένων
    data.forEach(row => {
        const tr = document.createElement('tr');

        // Προσθήκη κελιών δεδομένων
        Object.values(row).forEach(value => {
            const td = document.createElement('td');
            td.textContent = value !== null ? value : '';
            tr.appendChild(td);
        });

        // Προσθήκη κουμπιών ενεργειών
        const actionsTd = document.createElement('td');
        actionsTd.className = 'action-buttons';

        const editButton = document.createElement('button');
        editButton.className = 'edit-button';
        editButton.textContent = 'Επεξεργασία';
        editButton.onclick = () => showForm('Επεξεργασία', section, row);

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete-button';
        deleteButton.textContent = 'Διαγραφή';
        deleteButton.onclick = () => handleDelete(section, row);

        actionsTd.appendChild(editButton);
        actionsTd.appendChild(deleteButton);
        tr.appendChild(actionsTd);

        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    return table;
}

// ================ Pagination Functions ================

/**
 * Δημιουργεί το pagination component
 * @param {Object} paginationData - Δεδομένα σελιδοποίησης
 * @param {string} section - Η τρέχουσα ενότητα
 * @returns {HTMLElement} - Το pagination component
 */
function createPagination(paginationData, section) {
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination';

    // Κουμπί προηγούμενης σελίδας
    if (paginationData.currentPage > 1) {
        const prevButton = createPaginationButton('«', paginationData.currentPage - 1, section);
        paginationContainer.appendChild(prevButton);
    }

    // Σελίδες
    for (let i = 1; i <= paginationData.totalPages; i++) {
        if (shouldShowPageNumber(i, paginationData.currentPage, paginationData.totalPages)) {
            const pageButton = createPaginationButton(
                i.toString(),
                i,
                section,
                i === paginationData.currentPage
            );
            paginationContainer.appendChild(pageButton);
        } else if (shouldShowEllipsis(i, paginationData.currentPage, paginationData.totalPages)) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'pagination-dots';
            ellipsis.textContent = '...';
            paginationContainer.appendChild(ellipsis);
        }
    }

    // Κουμπί επόμενης σελίδας
    if (paginationData.currentPage < paginationData.totalPages) {
        const nextButton = createPaginationButton('»', paginationData.currentPage + 1, section);
        paginationContainer.appendChild(nextButton);
    }

    return paginationContainer;
}

/**
 * Δημιουργεί ένα κουμπί pagination
 * @param {string} text - Το κείμενο του κουμπιού
 * @param {number} page - Ο αριθμός σελίδας
 * @param {string} section - Η τρέχουσα ενότητα
 * @param {boolean} isActive - Αν είναι η τρέχουσα σελίδα
 * @returns {HTMLElement} - Το κουμπί pagination
 */
function createPaginationButton(text, page, section, isActive = false) {
    const button = document.createElement('a');
    button.href = '#';
    button.textContent = text;
    if (isActive) button.classList.add('active');

    button.onclick = (e) => {
        e.preventDefault();
        loadSection(section, page);
    };

    return button;
}

/**
 * Ελέγχει αν πρέπει να εμφανιστεί ο αριθμός σελίδας
 */
function shouldShowPageNumber(page, currentPage, totalPages) {
    return page === 1 ||
        page === totalPages ||
        (page >= currentPage - 1 && page <= currentPage + 1);
}

/**
 * Ελέγχει αν πρέπει να εμφανιστούν αποσιωπητικά
 */
function shouldShowEllipsis(page, currentPage, totalPages) {
    return (page === 2 && currentPage > 4) ||
        (page === totalPages - 1 && currentPage < totalPages - 3);
}

// ================ CRUD Operations ================

/**
 * Χειρίζεται τη διαγραφή εγγραφών
 * @param {string} section - Η τρέχουσα ενότητα
 * @param {Object} row - Τα δεδομένα της γραμμής προς διαγραφή
 */

async function handleDelete(section, row) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εγγραφή;')) {
        return;
    }

    try {
        showLoading();

        const paths = {
            'Ζώα': 'zwo/delete_zwo.php',
            'Εισιτήρια': 'eisitirio/delete_eisitirio.php',
            'Είδη': 'eidos/delete_eidi.php',
            'Ταμίες': 'tamias/delete_tamias.php',
            'Φροντιστές': 'frontisths/delete_frontisth.php',
            'Προμηθευτές': 'promhuefths/delete_promhuefth.php',
            'Επισκέπτες': 'episkepths/delete_episkepth.php',
            'Εκδηλώσεις': 'ekdilosi/delete_ekdilosi.php'
        };

        // Καταγραφή των δεδομένων που αποστέλλονται
        console.log('Δεδομένα που αποστέλλονται:', row);
        console.log('Path που χρησιμοποιείται:', paths[section]);

        const response = await fetch(paths[section], {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(row)
        });

        // Ανάγνωση της απόκρισης ως κείμενο για να καταγραφεί
        const text = await response.text();
        console.log('Απόκριση από τον server:', text);

        // Προσπάθεια μετατροπής της απόκρισης σε JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (error) {
            console.error('Η απόκριση δεν είναι έγκυρο JSON:', text);
            throw new Error('Η απόκριση δεν είναι έγκυρο JSON');
        }

        if (result.status === 'error') {
            throw new Error(result.message);
        }

        showMessage('Η διαγραφή ολοκληρώθηκε επιτυχώς');
        await loadSection(section);
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}


/**
 * Εμφανίζει τη φόρμα προσθήκης/επεξεργασίας
 * @param {string} formType - Ο τύπος της φόρμας (Προσθήκη/Επεξεργασία)
 * @param {string} section - Η τρέχουσα ενότητα
 * @param {Object} data - Τα δεδομένα προς επεξεργασία (null για προσθήκη)
 */
function showForm(formType, section, data = null) {
    const contentElement = document.getElementById('content');
    contentElement.innerHTML = '';

    const form = document.createElement('form');
    form.className = 'entity-form';
    form.onsubmit = (e) => handleFormSubmit(e, section, formType);

    const title = document.createElement('h2');
    title.textContent = `${formType} ${section}`;
    form.appendChild(title);

    const fields = getFormFields(section);
    fields.forEach(field => {
        const formGroup = createFormField(field, data?.[field.name]);
        form.appendChild(formGroup);
    });

    // Προσθήκη κουμπιών
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
    cancelButton.onclick = () => loadSection(section);
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    contentElement.appendChild(form);
}

function getFormFields(section) {
    switch (section) {
        case 'Ζώα':
            return [
                { name: 'kodikos', label: 'Κωδικός', required: true, pattern: '^Z\\d{6}$', type: 'text' },
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'etos_genesis', label: 'Έτος Γέννησης', required: true, type: 'number', min: 1900, max: new Date().getFullYear() },
                { name: 'onoma_eidous', label: 'Είδος', required: true, type: 'text' }
            ];
        case 'Εισιτήρια':
            return [
                { name: 'arithmos', label: 'Αριθμός Εισιτηρίου', required: true, pattern: '^TK\\d{4}$', type: 'text' },
                { name: 'email', label: 'Email Επισκέπτη', required: true, type: 'email' },
                { name: 'imerominia', label: 'Ημερομηνία', required: true, type: 'date' },
                { name: 'ora', label: 'Ώρα', required: true, type: 'time' },
                { name: 'timi', label: 'Τιμή', required: true, type: 'number', min: 0 },
                { name: 'idTamia', label: 'ID Ταμία', required: true, pattern: '^TM\\d{3}$', type: 'text' }
            ];
        case 'Είδη':
            return [
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'katigoria', label: 'Κατηγορία', required: true, type: 'text' },
                { name: 'perigrafi', label: 'Περιγραφή', required: false, type: 'textarea' }
            ];
        case 'Ταμίες':
            return [
                { name: 'id', label: 'ID', required: true, pattern: '^TM\\d{3}$', type: 'text' },
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
                { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
                { name: 'misthos', label: 'Μισθός', required: true, type: 'number', min: 0 }
            ];
        case 'Επισκέπτες':
            return [
                { name: 'email', label: 'Email', required: true, type: 'email' },
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
                { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' }
            ];
        case 'Φροντιστές':
            return [
                { name: 'id', label: 'ID', required: true, pattern: '^FR\\d{3}$', type: 'text' },
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
                { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
                { name: 'misthos', label: 'Μισθός', required: true, type: 'number', min: 0 }
            ];
        case 'Προμηθευτές':
            return [
                { name: 'afm', label: 'ΑΦΜ', required: true, pattern: '^\\d{9}$', type: 'text' },
                { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
                { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
                { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
                { name: 'dieuthinsi', label: 'Διεύθυνση', required: true, type: 'text' }
            ];
        case 'Εκδηλώσεις':
            return [
                { name: 'titlos', label: 'Τίτλος', required: true, type: 'text' },
                { name: 'hmerominia', label: 'Ημερομηνία', required: true, type: 'date' },
                { name: 'perigrafi', label: 'Περιγραφή', required: false, type: 'textarea' }
            ];
        default:
            return [];
    }
}

function createFormField(field, value = '') {
    const formGroup = document.createElement('div');
    formGroup.className = 'form-group';

    const label = document.createElement('label');
    label.textContent = field.label;
    if (field.required) label.classList.add('required');
    formGroup.appendChild(label);

    let input;
    if (field.type === 'textarea') {
        input = document.createElement('textarea');
        input.rows = 4;
    } else {
        input = document.createElement('input');
        input.type = field.type;
        if (field.pattern) input.pattern = field.pattern;
        if (field.min !== undefined) input.min = field.min;
        if (field.max !== undefined) input.max = field.max;
    }

    input.name = field.name;
    input.value = value;
    input.required = field.required;

    input.addEventListener('invalid', (e) => {
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();

        const error = document.createElement('div');
        error.className = 'error-message';
        error.textContent = getValidationMessage(field, input);
        formGroup.appendChild(error);
    });

    input.addEventListener('input', () => {
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    });

    formGroup.appendChild(input);
    return formGroup;
}

function getValidationMessage(field, input) {
    if (!input.value) return `Το πεδίο ${field.label} είναι υποχρεωτικό`;
    if (field.type === 'email' && input.validity.typeMismatch) return 'Μη έγκυρη διεύθυνση email';
    if (field.type === 'tel' && input.validity.patternMismatch) return 'Το τηλέφωνο πρέπει να έχει 10 ψηφία';
    if (field.pattern && input.validity.patternMismatch) {
        switch (field.name) {
            case 'kodikos': return 'Ο κωδικός πρέπει να ξεκινάει με Z και να ακολουθούν 6 ψηφία';
            case 'id': return 'Το ID πρέπει να ξεκινάει με FR ή TM και να ακολουθούν 3 ψηφία';
            case 'arithmos': return 'Ο αριθμός εισιτηρίου πρέπει να ξεκινάει με TK και να ακολουθούν 4 ψηφία';
            case 'afm': return 'Το ΑΦΜ πρέπει να αποτελείται από 9 ψηφία';
            default: return 'Μη έγκυρη μορφή';
        }
    }
    return 'Μη έγκυρη τιμή';
}
/**
 * Χειρίζεται την υποβολή της φόρμας
 * @param {Event} event - Το event της φόρμας
 * @param {string} section - Η τρέχουσα ενότητα
 * @param {string} formType - Ο τύπος της φόρμας
 */
async function handleFormSubmit(event, section, formType) {
    event.preventDefault();
    showLoading();

    try {
        const formData = new FormData(event.target);
        const url = `${formType === 'Προσθήκη' ? 'zwo/add_zwo.php' : 'zwo/update_zwo.php'}`;

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
        loadSection(section);
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
}

/**
 * Δημιουργεί πεδία της φόρμας ανάλογα με την ενότητα
 * @param {string} section - Η τρέχουσα ενότητα
 * @returns {Array} - Λίστα πεδίων της φόρμας
 */
function getFormFields(section) {
    const fields = {
        'Ζώα': [
            { name: 'kodikos', label: 'Κωδικός', required: true, pattern: '^Z\\d{6}$', type: 'text' },
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'etos_genesis', label: 'Έτος Γέννησης', required: true, type: 'number', min: 1900, max: new Date().getFullYear() },
            { name: 'onoma_eidous', label: 'Είδος', required: true, type: 'select', dataSource: 'get_species.php' }
        ],
        'Φροντιστές': [
            { name: 'id', label: 'ID', required: true, pattern: '^FR\\d{3}$', type: 'text' },
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
            { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
            { name: 'misthos', label: 'Μισθός', required: true, type: 'number', min: 0 }
        ],
        'Ταμίες': [
            { name: 'id', label: 'ID', required: true, pattern: '^TM\\d{3}$', type: 'text' },
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
            { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
            { name: 'misthos', label: 'Μισθός', required: true, type: 'number', min: 0 }
        ],
        'Εισιτήρια': [
            { name: 'KODIKOS', label: 'Κωδικός', required: true, type: 'text' },
            { name: 'HMEROMINIA_EKDOSHS', label: 'Ημερομηνία Έκδοσης', required: true, type: 'date' },
            { name: 'TIMI', label: 'Τιμή', required: true, type: 'number', min: 0 },
            { name: 'IDTAMIA', label: 'ID Ταμία', required: true, pattern: '^TM\\d{3}$', type: 'text' },
            { name: 'EMAIL', label: 'Email Επισκέπτη', required: true, type: 'email' },
            { name: 'KATIGORIA', label: 'Κατηγορία', required: true, type: 'text' },
            { name: 'ONOMA_TAMIA', label: 'Όνομα Ταμία', required: true, type: 'text' },
            { name: 'EPONYMO_TAMIA', label: 'Επώνυμο Ταμία', required: true, type: 'text' },
            { name: 'ONOMA_EPISKEPTH', label: 'Όνομα Επισκέπτη', required: true, type: 'text' },
            { name: 'EPONYMO_EPISKEPTH', label: 'Επώνυμο Επισκέπτη', required: true, type: 'text' }
        ],
        'Επισκέπτες': [
            { name: 'email', label: 'Email', required: true, type: 'email' },
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
            { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' }
        ],
        'Προμηθευτές': [
            { name: 'afm', label: 'ΑΦΜ', required: true, pattern: '^\\d{9}$', type: 'text' },
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' },
            { name: 'tilefono', label: 'Τηλέφωνο', required: true, pattern: '^\\d{10}$', type: 'tel' },
            { name: 'dieuthinsi', label: 'Διεύθυνση', required: true, type: 'text' }
        ],
        'Είδη': [
            { name: 'onoma', label: 'Όνομα', required: true, type: 'text' },
            { name: 'katigoria', label: 'Κατηγορία', required: true, type: 'text' },
            { name: 'perigrafi', label: 'Περιγραφή', required: false, type: 'textarea' }
        ],
        'Εκδηλώσεις': [
            { name: 'titlos', label: 'Τίτλος', required: true, type: 'text' },
            { name: 'hmerominia', label: 'Ημερομηνία', required: true, type: 'date' },
            { name: 'perigrafi', label: 'Περιγραφή', required: false, type: 'textarea' }
        ]
    };

    return fields[section] || [];
}

/**
 * Δημιουργεί ένα πεδίο της φόρμας
 * @param {Object} field - Το αντικείμενο του πεδίου
 * @param {string} value - Η αρχική τιμή του πεδίου
 * @returns {HTMLElement} - Το HTML στοιχείο του πεδίου
 */
function createFormField(field, value = '') {
    const formGroup = document.createElement('div');
    formGroup.className = 'form-group';

    const label = document.createElement('label');
    label.textContent = field.label;
    if (field.required) label.classList.add('required');
    formGroup.appendChild(label);

    let input;
    if (field.type === 'textarea') {
        input = document.createElement('textarea');
        input.rows = 4;
    } else if (field.type === 'select') {
        input = document.createElement('select');
        // Add loading placeholder
        const placeholder = document.createElement('option');
        placeholder.text = 'Φόρτωση...';
        placeholder.disabled = true;
        placeholder.selected = true;
        input.appendChild(placeholder);

        // Load options from server
        fetch(field.dataSource)
            .then(response => response.json())
            .then(data => {
                input.innerHTML = ''; // Clear loading placeholder
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.Onoma;
                    option.text = item.Onoma;
                    if (item.Onoma === value) option.selected = true;
                    input.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading options:', error);
                input.innerHTML = '<option disabled>Error loading options</option>';
            });
    } else {
        input = document.createElement('input');
        input.type = field.type;
        if (field.pattern) input.pattern = field.pattern;
        if (field.min !== undefined) input.min = field.min;
        if (field.max !== undefined) input.max = field.max;
    }

    input.name = field.name;
    input.value = value;
    input.required = field.required;

    // Add validation feedback
    input.addEventListener('invalid', (e) => {
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();

        const error = document.createElement('div');
        error.className = 'error-message';
        error.textContent = getValidationMessage(field, input);
        formGroup.appendChild(error);
    });

    input.addEventListener('input', () => {
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    });

    formGroup.appendChild(input);
    return formGroup;
}

function getValidationMessage(field, input) {
    if (!input.value) return `Το πεδίο ${field.label} είναι υποχρεωτικό`;
    if (field.type === 'email' && input.validity.typeMismatch) return 'Μη έγκυρη διεύθυνση email';
    if (field.type === 'tel' && input.validity.patternMismatch) return 'Το τηλέφωνο πρέπει να έχει 10 ψηφία';
    if (field.pattern && input.validity.patternMismatch) {
        switch (field.name) {
            case 'kodikos': return 'Ο κωδικός πρέπει να ξεκινάει με Z και να ακολουθούν 6 ψηφία';
            case 'id': return 'Το ID πρέπει να ξεκινάει με FR ή TM και να ακολουθούν 3 ψηφία';
            case 'afm': return 'Το ΑΦΜ πρέπει να αποτελείται από 9 ψηφία';
            default: return 'Μη έγκυρη μορφή';
        }
    }
    return 'Μη έγκυρη τιμή';
}
