<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    $required_fields = ['email', 'onoma', 'eponymo'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το email υπάρχει ήδη");
    }

    $stmt = $db->prepare("INSERT INTO EPISKEPTIS (Email, Onoma, Eponymo) VALUES (?, ?, ?)");
    $email = trim($_POST['email']);
    $onoma = trim(htmlspecialchars($_POST['onoma']));
    $eponymo = trim(htmlspecialchars($_POST['eponymo']));
    
    $stmt->bind_param("sss", $email, $onoma, $eponymo);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $db->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης καταχωρήθηκε επιτυχώς'
    ]);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}