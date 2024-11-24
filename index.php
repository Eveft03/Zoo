<?php
// Συμπερίληψη της σύνδεσης με τη βάση
include 'db_connection.php';

// Ερώτηση για ζώα
$sql_animals = "SELECT ZWO.Kodikos, ZWO.Onoma, ZWO.Etos_Genesis, EIDOS.Katigoria 
                FROM ZWO 
                INNER JOIN EIDOS ON ZWO.Onoma_Eidous = EIDOS.Onoma";
$result_animals = $conn->query($sql_animals);

// Ερώτηση για είδη
$sql_eidos = "SELECT * FROM EIDOS";
$result_eidos = $conn->query($sql_eidos);

// Ερώτηση για εκδηλώσεις
$sql_events = "SELECT * FROM EKDILOSI";
$result_events = $conn->query($sql_events);

// Ερώτηση για εισιτήρια
$sql_tickets = "SELECT EISITIRIO.Kodikos, EISITIRIO.Hmerominia_Ekdoshs, EISITIRIO.Timi, EPISKEPTIS.Onoma, EPISKEPTIS.Eponymo 
                FROM EISITIRIO
                INNER JOIN EPISKEPTIS ON EISITIRIO.Email = EPISKEPTIS.Email";
$result_tickets = $conn->query($sql_tickets);
?>
<!DOCTYPE html>
<html lang="el">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ζωολογικός Κήπος</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>

<body>
    <nav>
        <a href="#animals">Ζώα</a>
        <a href="#eidos">Είδη</a>
        <a href="#events">Εκδηλώσεις</a>
        <a href="#tickets">Εισιτήρια</a>
    </nav>

    <section id="animals">
        <h2>Λίστα Ζώων</h2>
        <table>
            <tr>
                <th>Κωδικός</th>
                <th>Όνομα</th>
                <th>Έτος Γέννησης</th>
                <th>Κατηγορία</th>
            </tr>
            <?php while ($row = $result_animals->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Kodikos']; ?></td>
                    <td><?= $row['Onoma']; ?></td>
                    <td><?= $row['Etos_Genesis']; ?></td>
                    <td><?= $row['Katigoria']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <section id="eidos">
        <h2>Είδη</h2>
        <table>
            <tr>
                <th>Όνομα</th>
                <th>Κατηγορία</th>
                <th>Περιγραφή</th>
            </tr>
            <?php while ($row = $result_eidos->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Onoma']; ?></td>
                    <td><?= $row['Katigoria']; ?></td>
                    <td><?= $row['Perigrafi']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <section id="events">
        <h2>Εκδηλώσεις</h2>
        <table>
            <tr>
                <th>Τίτλος</th>
                <th>Ημερομηνία</th>
                <th>Ώρα</th>
                <th>Χώρος</th>
            </tr>
            <?php while ($row = $result_events->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Titlos']; ?></td>
                    <td><?= $row['Hmerominia']; ?></td>
                    <td><?= $row['Ora']; ?></td>
                    <td><?= $row['Xwros']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <section id="tickets">
        <h2>Εισιτήρια</h2>
        <table>
            <tr>
                <th>Κωδικός</th>
                <th>Ημερομηνία Έκδοσης</th>
                <th>Τιμή</th>
                <th>Όνομα Επισκέπτη</th>
                <th>Επώνυμο Επισκέπτη</th>
            </tr>
            <?php while ($row = $result_tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Kodikos']; ?></td>
                    <td><?= $row['Hmerominia_Ekdoshs']; ?></td>
                    <td><?= $row['Timi']; ?></td>
                    <td><?= $row['Onoma']; ?></td>
                    <td><?= $row['Eponymo']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
</body>

</html>