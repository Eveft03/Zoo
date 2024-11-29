<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['AFM'])) {
        throw new Exception("Δεν βρέθηκε το ΑΦΜ του προμηθευτή");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο TROFIMO
    $stmt = $db->prepare("UPDATE TROFIMO SET AFM_PROMITHEUTI = NULL WHERE AFM_PROMITHEUTI = ?");
    $stmt->bind_param("s", $data['AFM']);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM PROMITHEUTIS WHERE AFM = ?");
    $stmt->bind_param("s", $data['AFM']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του προμηθευτή");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο προμηθευτής διαγράφηκε επιτυχώς'
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