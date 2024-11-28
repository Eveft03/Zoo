<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['arithmos']) || empty($_POST['arithmos'])) {
        throw new Exception("Απαιτείται ο αριθμός του εισιτηρίου");
    }

    $db->beginTransaction();

    // Delete ticket
    $stmt = $db->prepare("DELETE FROM EISITIRIO WHERE Arithmos = ?");
    $stmt->bind_param("s", $_POST['arithmos']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του εισιτηρίου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Το εισιτήριο δεν βρέθηκε");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Το εισιτήριο διαγράφηκε επιτυχώς'
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