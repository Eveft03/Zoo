// delete_eisitirio.php
<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Arithmos'])) {
        throw new Exception("Δεν καθορίστηκε το εισιτήριο προς διαγραφή");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("DELETE FROM EISITIRIO WHERE Arithmos = ?");
    $stmt->bind_param("s", $data['Arithmos']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του εισιτηρίου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Το εισιτήριο δεν βρέθηκε");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο διαγράφηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}