<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Email'])) {
        throw new Exception("Δεν βρέθηκε το email του επισκέπτη");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο EISITIRIO
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EISITIRIO WHERE Email = ?");
    $stmt->bind_param("s", $data['Email']);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Ο επισκέπτης δεν μπορεί να διαγραφεί γιατί έχει εισιτήρια");
    }

    $stmt = $db->prepare("DELETE FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $data['Email']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του επισκέπτη");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης διαγράφηκε επιτυχώς'
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