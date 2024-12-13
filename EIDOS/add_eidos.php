<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    // Έλεγχος απαιτούμενων πεδίων
    if (!isset($_POST['onoma']) || empty(trim($_POST['onoma']))) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
    }
    if (!isset($_POST['katigoria']) || empty(trim($_POST['katigoria']))) {
        throw new Exception("Η κατηγορία είναι υποχρεωτική");
    }
    if (!isset($_POST['perigrafi']) || empty(trim($_POST['perigrafi']))) {
        throw new Exception("Η περιγραφή είναι υποχρεωτική");
    }

    // Έλεγχος εγκυρότητας κατηγορίας
    $valid_categories = ['Θηλαστικά', 'Πουλιά', 'Ερπετά'];
    if (!in_array($_POST['katigoria'], $valid_categories)) {
        throw new Exception("Μη έγκυρη κατηγορία");
    }

    $db->begin_transaction();

    // Έλεγχος για διπλότυπο είδος
    $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
    $onoma_check = trim($_POST['onoma']);
    $stmt->bind_param("s", $onoma_check);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το είδος υπάρχει ήδη");
    }

    // Εισαγωγή είδους
    $stmt = $db->prepare("INSERT INTO EIDOS (Onoma, Katigoria, Perigrafi) VALUES (?, ?, ?)");
    
    $onoma = htmlspecialchars(trim($_POST['onoma']));
    $katigoria = htmlspecialchars(trim($_POST['katigoria']));
    $perigrafi = htmlspecialchars(trim($_POST['perigrafi']));
    
    $stmt->bind_param("sss", $onoma, $katigoria, $perigrafi);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του είδους: " . $stmt->error);
    }

    $db->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Το είδος προστέθηκε επιτυχώς'
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