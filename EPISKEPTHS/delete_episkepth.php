<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        throw new Exception("Απαιτείται το email του επισκέπτη");
    }

    $db->beginTransaction();

    // Check for dependencies in EISITIRIO table
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EISITIRIO WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        throw new Exception("Δεν μπορεί να γίνει διαγραφή του επισκέπτη καθώς υπάρχουν συνδεδεμένα εισιτήρια");
    }

    // Delete visitor
    $stmt = $db->prepare("DELETE FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του επισκέπτη");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο επισκέπτης δεν βρέθηκε");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης διαγράφηκε επιτυχώς'
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