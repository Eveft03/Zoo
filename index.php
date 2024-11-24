<?php
include 'db_connection.php';
$conn = connect();

$section = $_GET['section'] ?? '';

if ($section === 'Ζώα') {
    $result = $conn->query("SELECT * FROM ZWO");
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Κωδικός</th><th>Όνομα</th><th>Έτος Γέννησης</th><th>Είδος</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Kodikos']}</td><td>{$row['Onoma']}</td><td>{$row['Etos_Genesis']}</td><td>{$row['Onoma_Eidous']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Δεν βρέθηκαν δεδομένα για την ενότητα.";
    }
} elseif ($section === 'Είδη') {
    // Παρόμοιος κώδικας για τα είδη
} elseif ($section === 'addAnimal') {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $year = $_POST['year'];
    $type = $_POST['type'];

    $stmt = $conn->prepare("INSERT INTO ZWO (Kodikos, Onoma, Etos_Genesis, Onoma_Eidous) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $code, $name, $year, $type);

    if ($stmt->execute()) {
        echo "Το ζώο προστέθηκε με επιτυχία!";
    } else {
        echo "Πρόβλημα κατά την προσθήκη του ζώου.";
    }
    $stmt->close();
} else {
    echo "Η ενότητα που ζητήσατε δεν υπάρχει.";
}

$conn->close();
?>
