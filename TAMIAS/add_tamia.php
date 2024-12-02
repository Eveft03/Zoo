<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Έλεγχος απαιτούμενων πεδίων
    $required_fields = ['id', 'onoma', 'eponymo', 'tilefono', 'misthos'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!is_numeric($_POST['id']) || $_POST['id'] <= 0) {
        throw new Exception("Το ID πρέπει να είναι θετικός ακέραιος αριθμός");
    }

    // Έλεγχος τηλεφώνου
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Το τηλέφωνο πρέπει να περιέχει ακριβώς 10 ψηφία");
    }

    // Έλεγχος μισθού
    if (!is_numeric($_POST['misthos']) || $_POST['misthos'] < 0) {
        throw new Exception("Ο μισθός πρέπει να είναι θετικός αριθμός");
    }

    $db->beginTransaction();

    // Έλεγχος για διπλότυπο ID
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID ταμία υπάρχει ήδη");
    }

    // Εισαγωγή ταμία
    $stmt = $db->prepare("
        INSERT INTO TAMIAS (ID, Onoma, Eponymo, Tilefono, Misthos)
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
} finally {
    if (isset($db)) {
        $db->close();
    }
}