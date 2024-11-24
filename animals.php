<?php
// Συμπερίληψη της σύνδεσης
include 'db_connection.php';

// Ερώτηση για να φέρουμε όλα τα ζώα και το είδος τους
$sql = "SELECT ZWO.Kodikos, ZWO.Onoma, ZWO.Etos_Genesis, EIDOS.Katigoria, EIDOS.Perigrafi 
        FROM ZWO 
        INNER JOIN EIDOS ON ZWO.Onoma_Eidous = EIDOS.Onoma";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ζώα του Ζωολογικού Κήπου</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Λίστα Ζώων</h1>
    <table>
        <tr>
            <th>Κωδικός</th>
            <th>Όνομα</th>
            <th>Έτος Γέννησης</th>
            <th>Κατηγορία</th>
            <th>Περιγραφή</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['Kodikos'] . "</td>
                        <td>" . $row['Onoma'] . "</td>
                        <td>" . $row['Etos_Genesis'] . "</td>
                        <td>" . $row['Katigoria'] . "</td>
                        <td>" . $row['Perigrafi'] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Δεν υπάρχουν ζώα στη βάση δεδομένων.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
