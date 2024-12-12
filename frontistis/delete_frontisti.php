<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');


try {
    $db = getDatabase();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ID'])) {
        throw new Exception("Δεν βρέθηκε το ID");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM FRONTIZEI WHERE ID = ?");
    $stmt->bind_param("i", $data['ID']);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Δεν είναι δυνατή η διαγραφή. Ο φροντιστής έχει ανατεθειμένα ζώα.");
    }

    // Διαγραφή
    $stmt = $db->prepare("DELETE FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("i", $data['ID']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Επιτυχής διαγραφή']);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>