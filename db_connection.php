<?php
$servername = "localhost"; // Επειδή τρέχει τοπικά
$username = "root"; // Το προεπιλεγμένο username του MySQL
$password = ""; // Άφησε το κενό αν δεν έχεις βάλει κωδικό στον MySQL
$dbname = "ZWOLOGIKOS_KHPOS"; // Το όνομα της βάσης δεδομένων σου

// Δημιουργία σύνδεσης
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
