<?php
require_once 'db_connection.php';

$section = $_GET['section'] ?? 'animals';

if ($section === 'animals') {
    $query = "SELECT Kodikos, Onoma, Etos_Genesis, Onoma_Eidous FROM ZWO";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo "<h2>Λίστα Ζώων</h2>";
        echo "<table>";
        echo "<tr><th>Κωδικός</th><th>Όνομα</th><th>Έτος Γέννησης</th><th>Όνομα Είδους</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Kodikos']}</td><td>{$row['Onoma']}</td><td>{$row['Etos_Genesis']}</td><td>{$row['Onoma_Eidous']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Δεν υπάρχουν ζώα στη βάση δεδομένων.</p>";
    }
} else {
    echo "<p>Η ενότητα που ζητήσατε δεν υπάρχει.</p>";
}

$conn->close();
?>
