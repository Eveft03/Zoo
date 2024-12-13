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

    // Έλεγχος αν υπάρχει το ζώο
    $checkStmt = $db->prepare("SELECT 1 FROM ZWO WHERE Kodikos = ?");
    $checkStmt->bind_param("s", $data['Kodikos']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Το ζώο δεν βρέθηκε");
    }

    $db->begin_transaction();

    try {
        // Διαγραφή από FRONTIZEI
        $stmt = $db->prepare("DELETE FROM FRONTIZEI WHERE Kodikos = ?");
        $stmt->bind_param("s", $data['Kodikos']);
        $stmt->execute();

        // Διαγραφή από SYMMETEXEI
        $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Kodikos = ?");
        $stmt->bind_param("s", $data['Kodikos']);
        $stmt->execute();

        // Διαγραφή ζώου
        $stmt = $db->prepare("DELETE FROM ZWO WHERE Kodikos = ?");
        $stmt->bind_param("s", $data['Kodikos']);
        $stmt->execute();

        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Το ζώο διαγράφηκε επιτυχώς'
        ]);
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}