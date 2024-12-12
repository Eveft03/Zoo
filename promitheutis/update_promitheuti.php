<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['afm'])) {
        throw new Exception("Απαιτείται το ΑΦΜ του προμηθευτή");
    }
    $checkStmt = $db->prepare("SELECT 1 FROM PROMITHEUTIS WHERE AFM = ?");
    $checkStmt->bind_param("s", $_POST['afm']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
   throw new Exception("Ο προμηθευτής δεν βρέθηκε");
}

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['onoma']) && !empty($_POST['onoma'])) {
        $updates[] = "Onoma = ?";
        $types .= "s";
        $values[] = htmlspecialchars($_POST['onoma']);
    }

    if (isset($_POST['thlefono']) && !empty($_POST['thlefono'])) {
        if (!preg_match('/^\d{10}$/', $_POST['thlefono'])) {
            throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
        }
        $updates[] = "Thlefono = ?";
        $types .= "s";
        $values[] = $_POST['thlefono'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE PROMITHEUTIS SET " . implode(", ", $updates) . " WHERE AFM = ?";
    $types .= "s";
    $values[] = $_POST['afm'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του προμηθευτή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο προμηθευτής δεν βρέθηκε ή δεν έγιναν αλλαγές");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο προμηθευτής ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}