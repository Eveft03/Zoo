<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Μη έγκυρη μέθοδος αιτήματος'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Μη έγκυρα δεδομένα JSON'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


try {
    $db = getDatabase();
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Kodikos']) || empty($data['Kodikos'])) {
        throw new Exception("Δεν καθορίστηκε το ζώο προς διαγραφή");
    }

    // Begin transaction
    $db->beginTransaction();

    // Check for related records in SYMMETEXEI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM SYMMETEXEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception("Το ζώο δεν μπορεί να διαγραφεί γιατί συμμετέχει σε εκδηλώσεις");
    }

    // Check for related records in FRONTIZEI
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM FRONTIZEI WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception("Το ζώο δεν μπορεί να διαγραφεί γιατί έχει φροντιστές");
    }

    // Delete animal
    $stmt = $db->prepare("DELETE FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $data['Kodikos']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του ζώου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το ζώο για διαγραφή");
    }

    // Commit transaction
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