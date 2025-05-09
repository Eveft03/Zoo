<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    // Έλεγχος σύνδεσης με τη βάση
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    // Έλεγχος απαιτούμενων πεδίων
    $required_fields = ['kodikos', 'hmerominia_ekdoshs', 'timi', 'idTamia', 'email', 'katigoria'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος εγκυρότητας κωδικού εισιτηρίου
    if (!is_numeric($_POST['kodikos']) || $_POST['kodikos'] <= 0) {
        throw new Exception("Ο κωδικός πρέπει να είναι θετικός ακέραιος αριθμός");
    }

    // Έλεγχος ημερομηνίας (όχι Κυριακή)
    $date = new DateTime($_POST['hmerominia_ekdoshs']);
    if ($date->format('w') == 0) { // 0 = Κυριακή
        throw new Exception("Δεν επιτρέπεται η έκδοση εισιτηρίων την Κυριακή");
    }

    // Έλεγχος τιμής
    if (!is_numeric($_POST['timi']) || $_POST['timi'] <= 0) {
        throw new Exception("Μη έγκυρη τιμή");
    }

    // Έλεγχος ύπαρξης επισκέπτη
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο επισκέπτης με αυτό το email δεν υπάρχει");
    }

    // Έναρξη συναλλαγής
    $db->begin_transaction();

    // Έλεγχος για διπλότυπο εισιτήριο
    $stmt = $db->prepare("SELECT Kodikos FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $stmt->bind_param("is", $_POST['kodikos'], $_POST['hmerominia_ekdoshs']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Υπάρχει ήδη εισιτήριο με αυτόν τον κωδικό για την ίδια ημερομηνία");
    }

    // Έλεγχος αν υπάρχει ο ταμίας
    if ($_POST['idTamia'] !== 'null') {
        $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
        $stmt->bind_param("i", $_POST['idTamia']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Ο ταμίας με αυτό το ID δεν υπάρχει");
        }
    }

    // Έλεγχος για εκδηλώσεις αν η κατηγορία είναι "Με εκδήλωση"
    if ($_POST['katigoria'] === 'Με εκδήλωση') {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
        $stmt->bind_param("s", $_POST['hmerominia_ekdoshs']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] === 0) {
            throw new Exception("Δεν υπάρχουν εκδηλώσεις για την επιλεγμένη ημερομηνία");
        }
    }

    // Εισαγωγή εισιτηρίου
    $stmt = $db->prepare("
        INSERT INTO EISITIRIO (Kodikos, Hmerominia_Ekdoshs, Timi, IDTamia, Email, Katigoria)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $idTamia = $_POST['idTamia'] !== 'null' ? $_POST['idTamia'] : null;
    $stmt->bind_param("isdiss", 
        $_POST['kodikos'],
        $_POST['hmerominia_ekdoshs'],
        $_POST['timi'],
        $idTamia,
        $_POST['email'],
        $_POST['katigoria']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του εισιτηρίου: " . $stmt->error);
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Το εισιτήριο προστέθηκε επιτυχώς'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
