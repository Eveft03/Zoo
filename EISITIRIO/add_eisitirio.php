<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    $required_fields = ['arithmos', 'email', 'imerominia', 'ora', 'timi', 'idTamia'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Validate ticket number format (E.g., "TK0001")
    if (!preg_match('/^TK\d{4}$/', $_POST['arithmos'])) {
        throw new Exception("Ο αριθμός εισιτηρίου πρέπει να έχει τη μορφή 'TK' ακολουθούμενο από 4 ψηφία");
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $_POST['imerominia']);
    if (!$date || $date->format('Y-m-d') !== $_POST['imerominia']) {
        throw new Exception("Μη έγκυρη ημερομηνία");
    }

    // Validate time format
    if (!preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $_POST['ora'])) {
        throw new Exception("Μη έγκυρη ώρα");
    }

    // Validate price
    if (!is_numeric($_POST['timi']) || $_POST['timi'] <= 0) {
        throw new Exception("Μη έγκυρη τιμή");
    }

    $db->beginTransaction();

    // Check if ticket number already exists
    $stmt = $db->prepare("SELECT Arithmos FROM EISITIRIO WHERE Arithmos = ?");
    $stmt->bind_param("s", $_POST['arithmos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Ο αριθμός εισιτηρίου υπάρχει ήδη");
    }

    // Check if cashier exists
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['idTamia']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο ταμίας δεν βρέθηκε");
    }

    // Check if visitor exists
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο επισκέπτης δεν βρέθηκε");
    }

    // Insert ticket
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

    echo json_encode([
        'status' => 'success',
        'message' => 'Το εισιτήριο καταχωρήθηκε επιτυχώς'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>