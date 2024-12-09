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
    $checkStmt = $db->prepare("SELECT 1 FROM ZWO WHERE Kodikos = ?");
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
        $values[] = htmlspecialchars($_POST['onoma']);
    }

    if (isset($_POST['etos_genesis']) && !empty($_POST['etos_genesis'])) {
        $year = (int)$_POST['etos_genesis'];
        $current_year = date('Y');
        if ($year < 1900 || $year > $current_year) {
            throw new Exception("Μη έγκυρο έτος γέννησης");
        }
        $updates[] = "Etos_Genesis = ?";
        $types .= "i";
        $values[] = $year;
    }

    if (isset($_POST['onoma_eidous']) && !empty($_POST['onoma_eidous'])) {
        $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
        $stmt->bind_param("s", $_POST['onoma_eidous']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Το είδος δεν υπάρχει");
        }
        $updates[] = "Onoma_Eidous = ?";
        $types .= "s";
        $values[] = $_POST['onoma_eidous'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE ZWO SET " . implode(", ", $updates) . " WHERE Kodikos = ?";
    $types .= "s";
    $values[] = $_POST['kodikos'];
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του ζώου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Το ζώο δεν βρέθηκε ή δεν έγιναν αλλαγές");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το ζώο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}