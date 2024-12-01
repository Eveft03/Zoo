<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $required = ['kodikos', 'email', 'hmerominia_ekdoshs', 'timi', 'idTamia', 'katigoria'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!preg_match('/^[0-9]{5}$/', $_POST['kodikos'])) {
        throw new Exception("Ο κωδικός πρέπει να είναι 5ψήφιος αριθμός");
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    if (!is_numeric($_POST['timi']) || $_POST['timi'] <= 0) {
        throw new Exception("Μη έγκυρη τιμή");
    }

    $date = new DateTime($_POST['hmerominia_ekdoshs']);
    if ($date->format('w') == 0) {
        throw new Exception("Δεν επιτρέπεται η έκδοση εισιτηρίων την Κυριακή");
    }

    $db->beginTransaction();

    // Check for existing ticket
    $stmt = $db->prepare("SELECT Kodikos FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $stmt->bind_param("ss", $_POST['kodikos'], $_POST['hmerominia_ekdoshs']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Υπάρχει ήδη εισιτήριο με αυτόν τον κωδικό για την ίδια ημερομηνία");
    }

    // Verify email exists
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Δεν υπάρχει επισκέπτης με αυτό το email");
    }

    // Verify cashier exists
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['idTamia']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Δεν υπάρχει ταμίας με αυτό το ID");
    }

    // Check for events if ticket type is "Με εκδήλωση"
    if ($_POST['katigoria'] === 'Με εκδήλωση') {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
        $stmt->bind_param("s", $_POST['hmerominia_ekdoshs']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] === 0) {
            throw new Exception("Δεν υπάρχουν εκδηλώσεις για την επιλεγμένη ημερομηνία");
        }
    }

    // Insert ticket
    $stmt = $db->prepare("
        INSERT INTO EISITIRIO (
            Kodikos, Hmerominia_Ekdoshs, Timi, IDTamia, Email, Katigoria
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssdsss", 
        $_POST['kodikos'],
        $_POST['hmerominia_ekdoshs'],
        $_POST['timi'],
        $_POST['idTamia'],
        $_POST['email'],
        $_POST['katigoria']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του εισιτηρίου");
    }

    // Handle event assignments if ticket type is "Με εκδήλωση"
    if ($_POST['katigoria'] === 'Με εκδήλωση' && isset($_POST['ekdiloseis'])) {
        $stmt = $db->prepare("
            INSERT INTO APAITEI (Titlos, Kodikos, Hmerominia_Ekdoshs, Hmerominia)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($_POST['ekdiloseis'] as $ekdilosi) {
            $stmt->bind_param("ssss", 
                $ekdilosi['titlos'],
                $_POST['kodikos'],
                $_POST['hmerominia_ekdoshs'],
                $_POST['hmerominia_ekdoshs']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την ανάθεση εκδήλωσης στο εισιτήριο");
            }
        }
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>