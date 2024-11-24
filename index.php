<?php
include 'db_connection.php';

if (isset($_GET['section'])) {
    $section = $_GET['section'];

    if ($section === 'animals') {
        // Ενότητα Ζώα
        $sql = "SELECT ZWO.Kodikos, ZWO.Onoma, ZWO.Etos_Genesis, EIDOS.Katigoria 
                FROM ZWO 
                INNER JOIN EIDOS ON ZWO.Onoma_Eidous = EIDOS.Onoma";
        $result = $conn->query($sql);

        echo "<h2>Λίστα Ζώων</h2>";
        echo "<table>
                <tr>
                    <th>Κωδικός</th>
                    <th>Όνομα</th>
                    <th>Έτος Γέννησης</th>
                    <th>Κατηγορία</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Kodikos']}</td>
                    <td>{$row['Onoma']}</td>
                    <td>{$row['Etos_Genesis']}</td>
                    <td>{$row['Katigoria']}</td>
                  </tr>";
        }
        echo "</table>";
    } elseif ($section === 'eidos') {
        // Ενότητα Είδη
        $sql = "SELECT Onoma, Katigoria, Perigrafi FROM EIDOS";
        $result = $conn->query($sql);

        echo "<h2>Λίστα Ειδών</h2>";
        echo "<table>
                <tr>
                    <th>Όνομα</th>
                    <th>Κατηγορία</th>
                    <th>Περιγραφή</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Onoma']}</td>
                    <td>{$row['Katigoria']}</td>
                    <td>{$row['Perigrafi']}</td>
                  </tr>";
        }
        echo "</table>";
    } elseif ($section === 'events') {
        // Ενότητα Εκδηλώσεις
        $sql = "SELECT Titlos, Hmerominia, Ora, Xwros FROM EKDILOSI";
        $result = $conn->query($sql);

        echo "<h2>Λίστα Εκδηλώσεων</h2>";
        echo "<table>
                <tr>
                    <th>Τίτλος</th>
                    <th>Ημερομηνία</th>
                    <th>Ώρα</th>
                    <th>Χώρος</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Titlos']}</td>
                    <td>{$row['Hmerominia']}</td>
                    <td>{$row['Ora']}</td>
                    <td>{$row['Xwros']}</td>
                  </tr>";
        }
        echo "</table>";
    } elseif ($section === 'tickets') {
        // Ενότητα Εισιτήρια
        $sql = "SELECT Kodikos, Hmerominia_Ekdoshs, Timi, Katigoria 
                FROM EISITIRIO";
        $result = $conn->query($sql);

        echo "<h2>Λίστα Εισιτηρίων</h2>";
        echo "<table>
                <tr>
                    <th>Κωδικός</th>
                    <th>Ημερομηνία Έκδοσης</th>
                    <th>Τιμή</th>
                    <th>Κατηγορία</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Kodikos']}</td>
                    <td>{$row['Hmerominia_Ekdoshs']}</td>
                    <td>{$row['Timi']}</td>
                    <td>{$row['Katigoria']}</td>
                  </tr>";
        }
        echo "</table>";
    } elseif ($section === 'addAnimal') {
        // Εισαγωγή Νέου Ζώου
        $name = $_POST['name'];
        $code = $_POST['code'];
        $year = $_POST['year'];
        $type = $_POST['type'];

        $sql = "INSERT INTO ZWO (Kodikos, Onoma, Etos_Genesis, Onoma_Eidous) 
                VALUES ('$code', '$name', $year, '$type')";

        if ($conn->query($sql) === TRUE) {
            echo "Το ζώο προστέθηκε επιτυχώς!";
        } else {
            echo "Σφάλμα: " . $conn->error;
        }
    }
}
?>
