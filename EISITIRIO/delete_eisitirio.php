<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Kodikos']) || !isset($data['Hmerominia_Ekdoshs'])) {
        throw new Exception("Δεν βρέθηκαν τα στοιχεία του εισιτηρίου");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο APAITEI
    $stmt = $db->prepare("DELETE FROM APAITEI WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $stmt->bind_param("is", $data['Kodikos'], $data['Hmerominia_Ekdoshs']);
    $stmt->execute();

    // Διαγραφή εισιτηρίου
    $stmt = $db->prepare("DELETE FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $stmt->bind_param("is", $data['Kodikos'], $data['Hmerominia_Ekdoshs']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του εισιτηρίου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το εισιτήριο");
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