<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ID'])) {
        throw new Exception("Δεν βρέθηκε το ID του ταμία");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο EISITIRIO
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EISITIRIO WHERE IDTamia = ?");
    $stmt->bind_param("s", $data['ID']);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Ο ταμίας δεν μπορεί να διαγραφεί γιατί έχει εκδώσει εισιτήρια");
    }

    $stmt = $db->prepare("DELETE FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $data['ID']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του ταμία");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο ταμίας διαγράφηκε επιτυχώς'
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