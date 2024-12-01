// Import all operations
import { createZwoForm, handleZwoDelete } from './zwo/zwo-operations.js';
import { createTamiasForm, handleTamiasDelete } from './tamias/tamias-operations.js';
import { createFrontistisForm, handleFrontistisDelete } from './frontistis/frontistis-operations.js';
import { createEpiskeptisForm, handleEpiskeptisDelete } from './episkeptis/episkeptis-operations.js';
import { createEisitirioForm, handleEisitirioDelete } from './eisitirio/eisitirio-operations.js';
import { createEkdilosiForm, handleEkdilosiDelete } from './ekdilosi/ekdilosi-operations.js';
import { createEidiForm, handleEidiDelete } from './eidi/eidi-operations.js';

function showForm(formType, section, data = null) {
    const contentElement = document.getElementById('content');
    contentElement.innerHTML = '';

    let form;
    switch(section) {
        case 'Ζώα':
            form = createZwoForm(formType, data);
            break;
        case 'Ταμίες':
            form = createTamiasForm(formType, data);
            break;
        case 'Φροντιστές':
            form = createFrontistisForm(formType, data);
            break;
        case 'Επισκέπτες':
            form = createEpiskeptisForm(formType, data);
            break;
        case 'Εισιτήρια':
            form = createEisitirioForm(formType, data);
            break;
        case 'Εκδηλώσεις':
            form = createEkdilosiForm(formType, data);
            break;
        case 'Είδη':
            form = createEidiForm(formType, data);
            break;
        default:
            throw new Error('Άγνωστη ενότητα');
    }

    contentElement.appendChild(form);
}

// Update handleDelete function to use specific handlers
async function handleDelete(section, data) {
    switch(section) {
        case 'Ζώα':
            return handleZwoDelete(data);
        case 'Ταμίες':
            return handleTamiasDelete(data);
        case 'Φροντιστές':
            return handleFrontistisDelete(data);
        case 'Επισκέπτες':
            return handleEpiskeptisDelete(data);
        case 'Εισιτήρια':
            return handleEisitirioDelete(data);
        case 'Εκδηλώσεις':
            return handleEkdilosiDelete(data);
        case 'Είδη':
            return handleEidiDelete(data);
        default:
            throw new Error('Άγνωστη ενότητα');
    }
}

export {
    showForm,
    handleDelete
};