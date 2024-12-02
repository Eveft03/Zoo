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

    // Validate ID format
    if (!preg_match('/^FR\d{3}$/', $_POST['id'])) {
        throw new Exception("Το ID πρέπει να έχει τη μορφή 'FR' ακολουθούμενο από 3 ψηφία");
    }

    // Validate phone number
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
    }

    // Validate salary
    if (!is_numeric($_POST['misthos']) || $_POST['misthos'] <= 0) {
        throw new Exception("Μη έγκυρος μισθός");
    }

    $db->beginTransaction();

    // Check for duplicate ID
    $stmt = $db->prepare("SELECT ID FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID φροντιστή υπάρχει ήδη");
    }

    // Insert caretaker
    $stmt = $db->prepare("
        INSERT INTO FRONTISTIS (ID, Onoma, Eponymo, Tilefono, Misthos)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssssd", 
        $_POST['id'],
        htmlspecialchars($_POST['onoma']),
        htmlspecialchars($_POST['eponymo']),
        $_POST['tilefono'],
        $_POST['misthos']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του φροντιστή");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο φροντιστής προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>