<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['onoma'])) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
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
        $values[] = $_POST['katigoria'];
    }

    if (isset($_POST['perigrafi'])) {
        $updates[] = "Perigrafi = ?";
        $types .= "s";
        $values[] = htmlspecialchars($_POST['perigrafi']);
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE EIDOS SET " . implode(", ", $updates) . " WHERE Onoma = ?";
    $types .= "s";
    $values[] = $_POST['onoma'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του είδους");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Το είδος δεν βρέθηκε ή δεν έγιναν αλλαγές");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το είδος ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}