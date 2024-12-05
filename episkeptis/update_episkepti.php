<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required email
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        throw new Exception("Απαιτείται το email του επισκέπτη");
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
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

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    // Check if visitor exists
    $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Ο επισκέπτης δεν βρέθηκε");
    }

    // Prepare and execute update query
    $sql = "UPDATE EPISKEPTIS SET " . implode(", ", $updates) . " WHERE Email = ?";
    $stmt = $db->prepare($sql);
    
    $types .= "s"; // Add type for Email in WHERE clause
    $values[] = $_POST['email'];
    
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του επισκέπτη");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν έγιναν αλλαγές στον επισκέπτη");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης ενημερώθηκε επιτυχώς'
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