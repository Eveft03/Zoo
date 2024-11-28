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

        // Προσθήκη κουμπιού για νέα εγγραφή
        const addButton = document.createElement('button');
        addButton.className = 'add-button';
        addButton.textContent = `Προσθήκη ${section}`;
        addButton.onclick = () => showForm('Προσθήκη', section);
        contentElement.appendChild(addButton);

        if (data.data && data.data.length > 0) {
            // Εμφάνιση πληροφοριών σελιδοποίησης
            const infoDiv = document.createElement('div');
            infoDiv.className = 'pagination-info';
            const start = (data.pagination.currentPage - 1) * data.pagination.itemsPerPage + 1;
            const end = Math.min(start + data.pagination.itemsPerPage - 1, data.pagination.totalItems);
            infoDiv.textContent = `Εμφάνιση ${start}-${end} από ${data.pagination.totalItems} ${section}`;
            contentElement.appendChild(infoDiv);

            // Δημιουργία πίνακα δεδομένων
            const table = createDataTable(data.data, section);
            contentElement.appendChild(table);

            // Προσθήκη pagination αν υπάρχουν πολλές σελίδες
            if (data.pagination && data.pagination.totalPages > 1) {
                const pagination = createPagination(data.pagination, section);
                contentElement.appendChild(pagination);
            }
        } else {
            const noDataMessage = document.createElement('p');
            noDataMessage.textContent = 'Δεν βρέθηκαν δεδομένα';
            contentElement.appendChild(noDataMessage);
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
        const response = await fetch(`delete_${section.toLowerCase()}.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(row)
        });

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

    // Δημιουργία πεδίων φόρμας ανάλογα με την ενότητα
    const fields = getFormFields(section);
    fields.forEach(field => {
        const formGroup = createFormField(field, data?.[field.name]);
        form.appendChild(formGroup);
    });

    // Προσθήκη κουμπιών
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = formType;
    form.appendChild(submitButton);

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Ακύρωση';
    cancelButton.className = 'cancel-button';
    cancelButton.onclick = () => loadSection(section);
    form.appendChild(cancelButton);

    contentElement.appendChild(form);
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
        const url = `${formType === 'Προσθήκη' ? 'add' : 'update'}_${section.toLowerCase()}.php`;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

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