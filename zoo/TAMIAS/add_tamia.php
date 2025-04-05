<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    // Έλεγχος σύνδεσης με τη βάση
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    // Έλεγχος απαιτούμενων πεδίων
    $required_fields = ['id', 'onoma', 'eponymo'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος εγκυρότητας ID
    if (!is_numeric($_POST['id']) || $_POST['id'] <= 0) {
        throw new Exception("Το ID πρέπει να είναι θετικός ακέραιος αριθμός");
    }

    // Έναρξη συναλλαγής
    $db->begin_transaction();

    // Έλεγχος για διπλότυπο ID ταμία
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID ταμία υπάρχει ήδη");
    }

    // Εισαγωγή ταμία
    $stmt = $db->prepare("
        INSERT INTO TAMIAS (ID, Onoma, Eponymo)
        VALUES (?, ?, ?)
    ");
    $onoma = htmlspecialchars($_POST['onoma'], ENT_QUOTES, 'UTF-8');
    $eponymo = htmlspecialchars($_POST['eponymo'], ENT_QUOTES, 'UTF-8');

    $stmt->bind_param("iss", 
        $_POST['id'],
        $onoma,
        $eponymo
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ταμία: " . $stmt->error);
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο ταμίας προστέθηκε επιτυχώς'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

?>