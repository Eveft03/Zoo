<?php
ini_set('display_errors', 0); // Απενεργοποίηση εμφάνισης σφαλμάτων
error_reporting(0);

mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=utf-8');

require_once '../db_connection.php';

try {
    $db = getDatabase();
    
    if (!isset($_POST['id']) || !isset($_POST['onoma']) || !isset($_POST['eponymo'])) {
        throw new Exception("Λείπουν απαραίτητα πεδία");
    }

    $stmt = $db->prepare("INSERT INTO FRONTISTIS (ID, Onoma, Eponymo) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", 
        $_POST['id'],
        $_POST['onoma'],
        $_POST['eponymo']
    );
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο φροντιστής προστέθηκε επιτυχώς'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>