// tamias.js
import { createFormField } from '../ValidationFunctions.js';
import { loadSection, showMessage, showLoading, hideLoading } from '../script.js';

const tamiasFields = [
   { name: 'id', label: 'ID', required: true, type: 'number', min: 1 },
   { name: 'onoma', label: 'Όνομα', required: true, type: 'text' }, 
   { name: 'eponymo', label: 'Επώνυμο', required: true, type: 'text' }
];

function createTamiasForm(formType, data = null) {
   const form = document.createElement('form');
   form.className = 'entity-form';
   form.onsubmit = (e) => handleTamiasSubmit(e, formType);

   const title = document.createElement('h2');
   title.textContent = `${formType} Ταμία`;
   form.appendChild(title);

   tamiasFields.forEach(field => {
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
   cancelButton.onclick = () => loadSection('Ταμίες');
   buttonsDiv.appendChild(cancelButton);

   form.appendChild(buttonsDiv);
   return form;
}

async function handleTamiasSubmit(event, formType) {
    event.preventDefault();
    showLoading();
  
    try {
        const formData = new FormData(event.target);
        const url = formType === 'Προσθήκη' ? './tamias/add_tamia.php' : './tamias/update_tamia.php';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const text = await response.text();
        console.log('Raw response:', text);  // Εκτύπωσε την απάντηση για να δεις τι επιστρέφεται
        
        // Αν το text είναι έγκυρο JSON, συνέχισε με parse
        let result;
        try {
            result = JSON.parse(text);
        } catch (error) {
            console.error('JSON parsing error:', error.message);
            return;
        }
        if (result.status === 'error') throw new Error(result.message);
        
        showMessage(result.message, false);
        loadSection('Ταμίες');
        
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        hideLoading();
    }
  }

async function handleTamiasDelete(data) {
   if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον ταμία;')) {
       return;
   }

   try {
       showLoading();

       const response = await fetch('./tamias/delete_tamia.php', {
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
       await loadSection('Ταμίες');
   } catch (error) {
       showMessage(error.message, true);
   } finally {
       hideLoading();
   }
}

export {
   createTamiasForm,
   handleTamiasSubmit, 
   handleTamiasDelete,
   tamiasFields
};