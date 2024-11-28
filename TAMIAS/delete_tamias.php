<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("Απαιτείται το ID του ταμία");
    }

    $db->beginTransaction();

    // Check for dependencies in EISITIRIO table
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EISITIRIO WHERE IDTamia = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        throw new Exception("Δεν μπορεί να γίνει διαγραφή του ταμία καθώς υπάρχουν συνδεδεμένα εισιτήρια");
    }

    // Delete cashier
    $stmt = $db->prepare("DELETE FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του ταμία");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο ταμίας δεν βρέθηκε");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο ταμίας διαγράφηκε επιτυχώς'
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