<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Titlos']) || !isset($data['Hmerominia'])) {
        throw new Exception("Δεν βρέθηκαν τα στοιχεία της εκδήλωσης");
    }

    // Έλεγχος αν υπάρχει η εκδήλωση
    $checkStmt = $db->prepare("SELECT 1 FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $checkStmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Η εκδήλωση δεν βρέθηκε");
    }

    $db->begin_transaction();

    try {
        // Έλεγχος συνδεδεμένων εισιτηρίων
        $stmt = $db->prepare("
            SELECT e.Kodikos
            FROM EISITIRIO e
            JOIN APAITEI a ON e.Kodikos = a.Kodikos AND e.Hmerominia_Ekdoshs = a.Hmerominia_Ekdoshs
            WHERE a.Titlos = ? AND a.Hmerominia = ?
        ");
        $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $eisitiria = array();
            while ($row = $result->fetch_assoc()) {
                $eisitiria[] = $row['Kodikos'];
            }
            throw new Exception("Η εκδήλωση δεν μπορεί να διαγραφεί γιατί υπάρχουν συνδεδεμένα εισιτήρια: " . implode(", ", $eisitiria));
        }

        // Έλεγχος για συμμετέχοντα ζώα
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM SYMMETEXEI WHERE Titlos = ? AND Hmerominia = ?");
        $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
        $stmt->execute();
        $zwaCount = $stmt->get_result()->fetch_assoc()['count'];

        if ($zwaCount > 0) {
            throw new Exception("Η εκδήλωση δεν μπορεί να διαγραφεί γιατί συμμετέχουν $zwaCount ζώα σε αυτή");
        }

        // Διαγραφή από SYMMETEXEI
        $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Titlos = ? AND Hmerominia = ?");
        $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
        $stmt->execute();

        // Διαγραφή εκδήλωσης
        $stmt = $db->prepare("DELETE FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
        $stmt->bind_param("ss", $data['Titlos'], $data['Hmerominia']);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Δεν βρέθηκε η εκδήλωση");
        }

        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Η εκδήλωση διαγράφηκε επιτυχώς'
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

?>