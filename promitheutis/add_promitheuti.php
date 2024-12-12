<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    $required_fields = ['afm', 'onoma', 'thlefono'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!preg_match('/^\d{9}$/', $_POST['afm'])) {
        throw new Exception("Το ΑΦΜ πρέπει να αποτελείται από 9 ψηφία");
    }

    if (!preg_match('/^\d{10}$/', $_POST['thlefono'])) {
        throw new Exception("Το τηλέφωνο πρέπει να αποτελείται από 10 ψηφία");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT AFM FROM PROMITHEUTIS WHERE AFM = ?");
    $stmt->bind_param("s", $_POST['afm']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ΑΦΜ υπάρχει ήδη");
    }

    $stmt = $db->prepare("INSERT INTO PROMITHEUTIS (AFM, Onoma, Thlefono) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", 
        $_POST['afm'],
        $_POST['onoma'],
        $_POST['thlefono']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του προμηθευτή");
    }

    $db->commit();

    $response = [
        'status' => 'success',
        'message' => 'Ο προμηθευτής προστέθηκε επιτυχώς'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

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