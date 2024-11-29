<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Titlos']) || !isset($data['Hmerominia'])) {
        throw new Exception("Δεν βρέθηκε ο τίτλος ή η ημερομηνία της εκδήλωσης");
    }

    $db->beginTransaction();

    // Διαγραφή από SYMMETEXEI
    $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
    $stmt->execute();

    // Διαγραφή εκδήλωσης
    $stmt = $db->prepare("DELETE FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή της εκδήλωσης");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Η εκδήλωση διαγράφηκε επιτυχώς'
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