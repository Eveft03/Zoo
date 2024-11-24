// Φόρτωση δεδομένων μέσω AJAX
function loadSection(section) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `index.php?section=${section}`, true);
    xhr.onload = function () {
        if (this.status === 200) {
            document.getElementById("content").innerHTML = this.responseText;
        } else {
            document.getElementById("content").innerHTML = `<p>Η ενότητα που ζητήσατε δεν υπάρχει.</p>`;
        }
    };
    xhr.send();
}

// Εμφάνιση φόρμας προσθήκης ζώου
function showAddAnimalForm() {
    document.getElementById("content").innerHTML = ''; // Αδειάζει την ενότητα περιεχομένου
    document.getElementById("addAnimalFormContainer").style.display = "block";
}

// Υποβολή φόρμας προσθήκης ζώου
function addAnimal(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append("name", document.getElementById("newAnimalName").value);
    formData.append("code", document.getElementById("newAnimalCode").value);
    formData.append("year", document.getElementById("newAnimalYear").value);
    formData.append("type", document.getElementById("newAnimalType").value);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "index.php?section=addAnimal", true);
    xhr.onload = function () {
        if (this.status === 200) {
            alert(this.responseText);
            document.getElementById("addAnimalForm").reset();
            document.getElementById("addAnimalFormContainer").style.display = "none";
        } else {
            alert("Πρόβλημα κατά την προσθήκη του ζώου.");
        }
    };
    xhr.send(formData);
}

// Αυτόματη φόρτωση της αρχικής ενότητας "Ζώα"
document.addEventListener("DOMContentLoaded", function () {
    loadSection('Ζώα');
});
