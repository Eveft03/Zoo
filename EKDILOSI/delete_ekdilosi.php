<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input parameters
    if (!isset($_POST['titlos']) || !isset($_POST['hmerominia'])) {
        throw new Exception("Απαιτούνται ο τίτλος και η ημερομηνία της εκδήλωσης");
    }

    $titlos = $_POST['titlos'];
    $hmerominia = $_POST['hmerominia'];

    // Begin transaction
    $db->beginTransaction();

    // First delete any participation records (SYMMETEXEI table)
    $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $titlos, $hmerominia);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή των συμμετοχών: " . $stmt->error);
    }

    // Then delete the event itself
    $stmt = $db->prepare("DELETE FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $titlos, $hmerominia);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή της εκδήλωσης: " . $stmt->error);
    }

    // Check if any rows were actually deleted
    if ($stmt->affected_rows === 0) {
        throw new Exception("Η εκδήλωση δεν βρέθηκε");
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Η εκδήλωση διαγράφηκε επιτυχώς'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Rollback transaction if an error occurred
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