<?php
require_once 'db_connection.php';

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

    // Check if species already exists
    $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
    $stmt->bind_param("s", $_POST['onoma']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) {
        throw new Exception("Το είδος υπάρχει ήδη");
    }

    // Insert new species
    $stmt = $db->prepare("
        INSERT INTO EIDOS (Onoma, Katigoria, Perigrafi) 
        VALUES (?, ?, ?)
    ");
    
    $perigrafi = $_POST['perigrafi'] ?? null;
    $stmt->bind_param("sss", 
        $_POST['onoma'],
        $_POST['katigoria'],
        $perigrafi
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του είδους");
    }

    // Commit transaction
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