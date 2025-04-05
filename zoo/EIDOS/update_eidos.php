<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['onoma']) || empty(trim($_POST['onoma']))) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
    }

    $db->begin_transaction();

    // Έλεγχος αν υπάρχει το είδος
    $checkStmt = $db->prepare("SELECT 1 FROM EIDOS WHERE Onoma = ?");
    $checkStmt->bind_param("s", $_POST['onoma']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Το είδος δεν βρέθηκε");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['katigoria']) && !empty($_POST['katigoria'])) {
        $valid_categories = ['Θηλαστικά', 'Πουλιά', 'Ερπετά'];
        if (!in_array($_POST['katigoria'], $valid_categories)) {
            throw new Exception("Μη έγκυρη κατηγορία");
        }
        $updates[] = "Katigoria = ?";
        $types .= "s";
        $values[] = htmlspecialchars(trim($_POST['katigoria']));
    }

    if (isset($_POST['perigrafi']) && !empty($_POST['perigrafi'])) {
        $updates[] = "Perigrafi = ?";
        $types .= "s";
        $values[] = htmlspecialchars(trim($_POST['perigrafi']));
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $sql = "UPDATE EIDOS SET " . implode(", ", $updates) . " WHERE Onoma = ?";
    $types .= "s";
    $values[] = $_POST['onoma'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του είδους: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν έγιναν αλλαγές");
    }

    $db->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Το είδος ενημερώθηκε επιτυχώς'
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