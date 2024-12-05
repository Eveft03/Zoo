<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("Απαιτείται το ID του ταμία");
    }

    // Build update query dynamically based on provided fields
    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['onoma']) && !empty($_POST['onoma'])) {
        $updates[] = "Onoma = ?";
        $types .= "s";
        $values[] = $_POST['onoma'];
    }

    if (isset($_POST['eponymo']) && !empty($_POST['eponymo'])) {
        $updates[] = "Eponymo = ?";
        $types .= "s";
        $values[] = $_POST['eponymo'];
    }

    if (isset($_POST['tilefono']) && !empty($_POST['tilefono'])) {
        if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
            throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
        }
        $updates[] = "Tilefono = ?";
        $types .= "s";
        $values[] = $_POST['tilefono'];
    }

    if (isset($_POST['misthos']) && !empty($_POST['misthos'])) {
        if (!is_numeric($_POST['misthos']) || $_POST['misthos'] <= 0) {
            throw new Exception("Μη έγκυρος μισθός");
        }
        $updates[] = "Misthos = ?";
        $types .= "d";
        $values[] = $_POST['misthos'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    // Check if cashier exists
    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("s", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο ταμίας δεν βρέθηκε");
    }

    // Prepare and execute update query
    $sql = "UPDATE TAMIAS SET " . implode(", ", $updates) . " WHERE ID = ?";
    $stmt = $db->prepare($sql);
    
    $types .= "s"; // Add type for ID in WHERE clause
    $values[] = $_POST['id'];
    
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του ταμία");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν έγιναν αλλαγές στον ταμία");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο ταμίας ενημερώθηκε επιτυχώς'
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