<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['id'])) {
        throw new Exception("Απαιτείται το ID του φροντιστή");
    }

    if (!preg_match('/^FR\d{3}$/', $_POST['id'])) {
        throw new Exception("Το ID πρέπει να έχει τη μορφή 'FR' ακολουθούμενο από 3 ψηφία");
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

    $sql = "UPDATE FRONTISTIS SET " . implode(", ", $updates) . " WHERE ID = ?";
    $types .= "s";
    $values[] = $_POST['id'];
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του φροντιστή");
    }

    if (isset($_POST['zwa'])) {
        $stmt = $db->prepare("DELETE FROM FRONTIZEI WHERE ID = ?");
        $stmt->bind_param("s", $_POST['id']);
        $stmt->execute();

        if (is_array($_POST['zwa']) && !empty($_POST['zwa'])) {
            $stmt = $db->prepare("INSERT INTO FRONTIZEI (ID, Kodikos) VALUES (?, ?)");
            foreach ($_POST['zwa'] as $kodikos) {
                $stmt->bind_param("ss", $_POST['id'], $kodikos);
                if (!$stmt->execute()) {
                    throw new Exception("Σφάλμα κατά την ανάθεση ζώου");
                }
            }
        }
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο φροντιστής ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>