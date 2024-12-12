<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Kodikos'])) {
        throw new Exception("Δεν βρέθηκε ο κωδικός του ζώου");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο SYMMETEXEI
    $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();

    // Έλεγχος εξαρτήσεων στο FRONTIZEI
    $stmt = $db->prepare("DELETE FROM FRONTIZEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();

    // Διαγραφή ζώου
    $stmt = $db->prepare("DELETE FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του ζώου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το ζώο");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Το ζώο διαγράφηκε επιτυχώς'
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