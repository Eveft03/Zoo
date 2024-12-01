<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    $db = getDatabase();

    // Έλεγχος απαιτούμενων πεδίων
    $required_fields = ['id', 'onoma', 'eponymo', 'tilefono', 'misthos'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος ID για θετικούς ακέραιους αριθμούς
    if (!filter_var($_POST['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        throw new Exception("Το ID πρέπει να είναι θετικός ακέραιος αριθμός.");
    }

    // Έλεγχος τηλεφώνου (ακριβώς 10 ψηφία)
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Το τηλέφωνο πρέπει να περιέχει μόνο 10 ψηφία.");
    }

    // Έλεγχος μισθού για μη αρνητικούς αριθμούς
    if (!filter_var($_POST['misthos'], FILTER_VALIDATE_FLOAT) || $_POST['misthos'] < 0) {
        throw new Exception("Μη έγκυρος μισθός.");
    }

    $db->beginTransaction();

    // Έλεγχος αν το ID υπάρχει ήδη
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID ταμία υπάρχει ήδη.");
    }

    // Εισαγωγή ταμία
    $stmt = $db->prepare("
        INSERT INTO TAMIAS (ID, Onoma, Eponymo, Tilefono, Misthos)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssd", 
        $_POST['id'], 
        $_POST['onoma'], 
        $_POST['eponymo'], 
        $_POST['tilefono'], 
        $_POST['misthos']
    );

    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ταμία.");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο ταμίας καταχωρήθηκε επιτυχώς.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }

    error_log("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

