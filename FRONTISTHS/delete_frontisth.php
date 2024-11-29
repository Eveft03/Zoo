<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ID'])) {
        throw new Exception("Δεν βρέθηκε το ID του φροντιστή");
    }

    $db->beginTransaction();

    // Διαγραφή από FRONTIZEI
    $stmt = $db->prepare("DELETE FROM FRONTIZEI WHERE ID = ?");
    $stmt->bind_param("s", $data['ID']);
    $stmt->execute();

    // Διαγραφή φροντιστή
    $stmt = $db->prepare("DELETE FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("s", $data['ID']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του φροντιστή");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο φροντιστής διαγράφηκε επιτυχώς'
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