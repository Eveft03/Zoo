<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Λήψη JSON δεδομένων
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Kodikos'])) {
        throw new Exception("Δεν βρέθηκε ο κωδικός του ζώου");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στον πίνακα SYMMETEXEI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM SYMMETEXEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception("Το ζώο δεν μπορεί να διαγραφεί γιατί συμμετέχει σε εκδηλώσεις");
    }

    // Έλεγχος εξαρτήσεων στον πίνακα FRONTIZEI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM FRONTIZEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception("Το ζώο δεν μπορεί να διαγραφεί γιατί έχει φροντιστές");
    }

    // Διαγραφή ζώου
    $stmt = $db->prepare("DELETE FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του ζώου");
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