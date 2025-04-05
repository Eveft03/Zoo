<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Kodikos']) || !isset($data['Hmerominia_Ekdoshs'])) {
        throw new Exception("Δεν βρέθηκαν τα στοιχεία του εισιτηρίου");
    }

    // Έλεγχος αν υπάρχει το εισιτήριο
    $checkStmt = $db->prepare("SELECT 1 FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $checkStmt->bind_param("is", $data['Kodikos'], $data['Hmerominia_Ekdoshs']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Το εισιτήριο δεν βρέθηκε");
    }

    $db->begin_transaction();

    try {
        // Διαγραφή από APAITEI
        $stmt = $db->prepare("DELETE FROM APAITEI WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
        $stmt->bind_param("is", $data['Kodikos'], $data['Hmerominia_Ekdoshs']);
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά τη διαγραφή από τον πίνακα APAITEI: " . $stmt->error);
        }

        // Διαγραφή από EISITIRIO
        $stmt = $db->prepare("DELETE FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
        $stmt->bind_param("is", $data['Kodikos'], $data['Hmerominia_Ekdoshs']);
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά τη διαγραφή του εισιτηρίου: " . $stmt->error);
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
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
