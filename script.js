// Φόρτωση δεδομένων και ενημέρωση active class
function loadSection(section) {
    // Απόκρυψη φόρμας προσθήκης ζώου όταν αλλάζει η ενότητα
    document.getElementById("addAnimalFormContainer").style.display = "none";

    // Ενημέρωση active class
    const links = document.querySelectorAll("nav a");
    links.forEach(link => link.classList.remove("active"));

    const activeLink = Array.from(links).find(link => link.textContent === section);
    if (activeLink) activeLink.classList.add("active");

    // AJAX για φόρτωση περιεχομένου
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `index.php?section=${section.toLowerCase()}`, true);
    xhr.onload = function () {
        if (this.status === 200 && this.responseText.trim() !== '') {
            document.getElementById("content").innerHTML = this.responseText;
        } else {
            document.getElementById("content").innerHTML = "<p>Δεν βρέθηκαν δεδομένα για την ενότητα.</p>";
        }
    };
    xhr.onerror = function () {
        document.getElementById("content").innerHTML = "<p>Σφάλμα σύνδεσης με τον server.</p>";
    };
    xhr.send();
}

// Εμφάνιση φόρμας προσθήκης ζώου
function showAddAnimalForm() {
    document.getElementById("content").innerHTML = ''; // Αδειάζει την ενότητα περιεχομένου
    document.getElementById("addAnimalFormContainer").style.display = "block";

    // Ενημέρωση active class
    const links = document.querySelectorAll("nav a");
    links.forEach(link => link.classList.remove("active"));
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
        }
    };
    xhr.onerror = function () {
        alert("Σφάλμα κατά την υποβολή της φόρμας.");
    };
    xhr.send(formData);
}

// Φόρτωση αρχικών δεδομένων
document.addEventListener("DOMContentLoaded", function () {
    loadSection('Ζώα');
});
