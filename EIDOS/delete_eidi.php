<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Onoma'])) {
        throw new Exception("Δεν βρέθηκε το όνομα του είδους");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο ZWO
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM ZWO WHERE Onoma_Eidous = ?");
    $stmt->bind_param("s", $data['Onoma']);
    $stmt->execute();
    
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Το είδος δεν μπορεί να διαγραφεί γιατί υπάρχουν ζώα αυτού του είδους");
    }

    // Έλεγχος εξαρτήσεων στο TREFETAI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM TREFETAI WHERE Eidos_onoma = ?");
    $stmt->bind_param("s", $data['Onoma']);
    $stmt->execute();
    
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Το είδος δεν μπορεί να διαγραφεί γιατί συνδέεται με τροφές");
    }

    // Διαγραφή είδους
    $stmt = $db->prepare("DELETE FROM EIDOS WHERE Onoma = ?");
    $stmt->bind_param("s", $data['Onoma']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του είδους");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το είδος");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Το είδος διαγράφηκε επιτυχώς'
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