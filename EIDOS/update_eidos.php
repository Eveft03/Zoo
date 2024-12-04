<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['onoma']) || empty($_POST['onoma'])) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
    }
    if (!isset($_POST['katigoria']) || empty($_POST['katigoria'])) {
        throw new Exception("Η κατηγορία είναι υποχρεωτική");
    }

    // Begin transaction
    $db->beginTransaction();

    // Update species
    $stmt = $db->prepare("
        UPDATE EIDOS 
        SET Katigoria = ?, 
            Perigrafi = ?
        WHERE Onoma = ?
    ");
    
    $perigrafi = $_POST['perigrafi'] ?? null;
    $stmt->bind_param("sss", 
        $_POST['katigoria'],
        $perigrafi,
        $_POST['onoma']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του είδους");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το είδος για ενημέρωση");
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Το είδος ενημερώθηκε επιτυχώς'
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