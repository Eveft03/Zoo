<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    if (!isset($_POST['kodikos'])) {
        throw new Exception("Απαιτείται ο κωδικός του ζώου");
    }

    // Build update query dynamically based on provided fields
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
        // Verify that the species exists
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

    // Check if animal exists
    $stmt = $db->prepare("SELECT Kodikos FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $_POST['kodikos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Το ζώο δεν βρέθηκε");
    }

    // Update animal
    $sql = "UPDATE ZWO SET " . implode(", ", $updates) . " WHERE Kodikos = ?";
    $types .= "s";
    $values[] = $_POST['kodikos'];
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του ζώου");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το ζώο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>