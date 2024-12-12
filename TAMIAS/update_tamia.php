<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['id'])) {
        throw new Exception("Απαιτείται το ID του ταμία");
    }
     // Πρόσθεσε τον έλεγχο ύπαρξης εδώ
     $checkStmt = $db->prepare("SELECT 1 FROM TAMIAS WHERE ID = ?");
     $checkStmt->bind_param("i", $_POST['id']);
     $checkStmt->execute();
     if ($checkStmt->get_result()->num_rows === 0) {
         throw new Exception("Ο ταμίας δεν βρέθηκε");
     }
    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['onoma']) && !empty($_POST['onoma'])) {
        $updates[] = "Onoma = ?";
        $types .= "s";
        $values[] = htmlspecialchars($_POST['onoma']);
    }

    if (isset($_POST['eponymo']) && !empty($_POST['eponymo'])) {
        $updates[] = "Eponymo = ?";
        $types .= "s";
        $values[] = htmlspecialchars($_POST['eponymo']);
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE TAMIAS SET " . implode(", ", $updates) . " WHERE ID = ?";
    $types .= "i";
    $values[] = $_POST['id'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του ταμία");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Ο ταμίας δεν βρέθηκε ή δεν έγιναν αλλαγές");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο ταμίας ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}