<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['kodikos'])) {
        throw new Exception("Απαιτείται ο κωδικός του ζώου");
    }

    $checkStmt = $db->prepare("SELECT * FROM ZWO WHERE Kodikos = ?");
    $checkStmt->bind_param("s", $_POST['kodikos']);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Το ζώο δεν βρέθηκε");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['onoma']) && !empty($_POST['onoma'])) {
        $updates[] = "Onoma = ?";
        $types .= "s";
        $values[] = $_POST['onoma'];
    }

    if (isset($_POST['etos_genesis']) && !empty($_POST['etos_genesis'])) {
        $updates[] = "Etos_Genesis = ?";
        $types .= "i";
        $values[] = intval($_POST['etos_genesis']);
    }

    if (isset($_POST['onoma_eidous']) && !empty($_POST['onoma_eidous'])) {
        $updates[] = "Onoma_Eidous = ?";
        $types .= "s";
        $values[] = $_POST['onoma_eidous'];
    }

    $sql = "UPDATE ZWO SET " . implode(", ", $updates) . " WHERE Kodikos = ?";
    $types .= "s";
    $values[] = $_POST['kodikos'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    echo json_encode(['status' => 'success', 'message' => 'Το ζώο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>