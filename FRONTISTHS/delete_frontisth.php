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

    // Έλεγχος εξαρτήσεων στο FRONTIZEI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM FRONTIZEI WHERE ID = ?");
    $stmt->bind_param("i", $data['ID']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception("Ο φροντιστής δεν μπορεί να διαγραφεί γιατί φροντίζει ζώα");
    }

    $stmt = $db->prepare("DELETE FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("i", $data['ID']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του φροντιστή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε φροντιστής με το συγκεκριμένο ID");
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