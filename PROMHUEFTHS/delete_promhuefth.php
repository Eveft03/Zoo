<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['afm']) || empty($_POST['afm'])) {
        throw new Exception("Απαιτείται το ΑΦΜ του προμηθευτή");
    }

    $db->beginTransaction();

    // First update TROFIMO table to remove references
    $stmt = $db->prepare("UPDATE TROFIMO SET AFM_PROMITHEUTI = NULL WHERE AFM_PROMITHEUTI = ?");
    $stmt->bind_param("s", $_POST['afm']);
    $stmt->execute();

    // Then delete the supplier
    $stmt = $db->prepare("DELETE FROM PROMITHEUTIS WHERE AFM = ?");
    $stmt->bind_param("s", $_POST['afm']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του προμηθευτή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο προμηθευτής δεν βρέθηκε");
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