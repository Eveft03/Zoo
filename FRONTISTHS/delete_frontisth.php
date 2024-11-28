<?php
// delete_caretaker.php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("Απαιτείται το ID του φροντιστή");
    }

    $db->beginTransaction();

    // First delete from FRONTIZEI table
    $stmt = $db->prepare("DELETE FROM FRONTIZEI WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();

    // Then delete the caretaker
    $stmt = $db->prepare("DELETE FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του φροντιστή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο φροντιστής δεν βρέθηκε");
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