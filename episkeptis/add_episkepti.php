<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    $required_fields = ['email', 'onoma', 'eponymo', 'tilefono'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
    }

    $db->beginTransaction();

    // Check if email already exists
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το email υπάρχει ήδη");
    }

    // Insert visitor
    $stmt = $db->prepare("
        INSERT INTO EPISKEPTIS (Email, Onoma, Eponymo, Tilefono) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssss", 
        $_POST['email'],
        $_POST['onoma'],
        $_POST['eponymo'],
        $_POST['tilefono']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του επισκέπτη");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης καταχωρήθηκε επιτυχώς'
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