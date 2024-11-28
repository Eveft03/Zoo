<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    $required_fields = ['id', 'onoma', 'eponymo', 'tilefono', 'misthos'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Validate ID format (e.g., "TM001")
    if (!preg_match('/^TM\d{3}$/', $_POST['id'])) {
        throw new Exception("Το ID πρέπει να έχει τη μορφή 'TM' ακολουθούμενο από 3 ψηφία");
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
    }

    // Validate salary
    if (!is_numeric($_POST['misthos']) || $_POST['misthos'] <= 0) {
        throw new Exception("Μη έγκυρος μισθός");
    }

    $db->beginTransaction();

    // Check if ID already exists
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID ταμία υπάρχει ήδη");
    }

    // Insert cashier
    $stmt = $db->prepare("
        INSERT INTO TAMIAS (ID, Onoma, Eponymo, Tilefono, Misthos) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssssd", 
        $_POST['id'],
        $_POST['onoma'],
        $_POST['eponymo'],
        $_POST['tilefono'],
        $_POST['misthos']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ταμία");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο ταμίας καταχωρήθηκε επιτυχώς'
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