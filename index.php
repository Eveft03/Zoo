<?php
include 'db_connection.php';

// Έλεγχος για την παράμετρο "section"
if (isset($_GET['section'])) {
    $section = $_GET['section'];

    if ($section === 'animals') {
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
        $sql = "SELECT * FROM EIDOS";
        $result = $conn->query($sql);

        echo "<h2>Είδη</h2>";
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
        $sql = "SELECT * FROM EKDILOSI";
        $result = $conn->query($sql);

        echo "<h2>Εκδηλώσεις</h2>";
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
        $sql = "SELECT EISITIRIO.Kodikos, EISITIRIO.Hmerominia_Ekdoshs, EISITIRIO.Timi, EPISKEPTIS.Onoma, EPISKEPTIS.Eponymo 
                FROM EISITIRIO
                INNER JOIN EPISKEPTIS ON EISITIRIO.Email = EPISKEPTIS.Email";
        $result = $conn->query($sql);

        echo "<h2>Εισιτήρια</h2>";
        echo "<table>
                <tr>
                    <th>Κωδικός</th>
                    <th>Ημερομηνία Έκδοσης</th>
                    <th>Τιμή</th>
                    <th>Όνομα Επισκέπτη</th>
                    <th>Επώνυμο Επισκέπτη</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Kodikos']}</td>
                    <td>{$row['Hmerominia_Ekdoshs']}</td>
                    <td>{$row['Timi']}</td>
                    <td>{$row['Onoma']}</td>
                    <td>{$row['Eponymo']}</td>
                  </tr>";
        }
        echo "</table>";
    }
}
?>
