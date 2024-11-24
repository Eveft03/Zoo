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