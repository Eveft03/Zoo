// add_eisitirio.php
<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Έλεγχος απαιτούμενων πεδίων
    $required = ['arithmos', 'email', 'imerominia', 'ora', 'timi', 'idTamia'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος μορφής αριθμού εισιτηρίου
    if (!preg_match('/^TK\d{4}$/', $_POST['arithmos'])) {
        throw new Exception("Μη έγκυρος αριθμός εισιτηρίου (πρέπει να είναι της μορφής TK####)");
    }

    // Έλεγχος email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    $db->beginTransaction();
    
    // Έλεγχος για υπάρχον εισιτήριο
    $stmt = $db->prepare("SELECT Arithmos FROM EISITIRIO WHERE Arithmos = ?");
    $stmt->bind_param("s", $_POST['arithmos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το εισιτήριο υπάρχει ήδη");
    }

    // Έλεγχος ύπαρξης ταμία
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['idTamia']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο ταμίας δεν βρέθηκε");
    }

    // Έλεγχος ύπαρξης επισκέπτη
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο επισκέπτης δεν βρέθηκε");
    }

    // Εισαγωγή εισιτηρίου
    $stmt = $db->prepare("
        INSERT INTO EISITIRIO (Arithmos, Email, Imerominia, Ora, Timi, IDTamia) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssssds", 
        $_POST['arithmos'],
        $_POST['email'],
        $_POST['imerominia'],
        $_POST['ora'],
        $_POST['timi'],
        $_POST['idTamia']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του εισιτηρίου");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}