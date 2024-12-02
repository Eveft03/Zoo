<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['afm']) || empty($_POST['afm'])) {
        throw new Exception("Απαιτείται το ΑΦΜ του προμηθευτή");
    }

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

    if (isset($_POST['dieuthinsi']) && !empty($_POST['dieuthinsi'])) {
        $updates[] = "Dieuthinsi = ?";
        $types .= "s";
        $values[] = $_POST['dieuthinsi'];
    }

    $db->beginTransaction();

    // Update supplier info if there are any changes
    if (!empty($updates)) {
        $sql = "UPDATE promitheutis SET " . implode(", ", $updates) . " WHERE AFM = ?";
        $types .= "s";
        $values[] = $_POST['afm'];
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του προμηθευτή");
        }
    }

    // Update food items if provided
    if (isset($_POST['trofima'])) {
        // First remove all current assignments
        $stmt = $db->prepare("UPDATE TROFIMO SET AFM_PROMITHEUTI = NULL WHERE AFM_PROMITHEUTI = ?");
        $stmt->bind_param("s", $_POST['afm']);
        $stmt->execute();

        // Then add new assignments
        if (is_array($_POST['trofima']) && !empty($_POST['trofima'])) {
            $stmt = $db->prepare("UPDATE TROFIMO SET AFM_PROMITHEUTI = ? WHERE Onoma = ?");
            foreach ($_POST['trofima'] as $trofimo) {
                $stmt->bind_param("ss", $_POST['afm'], $trofimo);
                if (!$stmt->execute()) {
                    throw new Exception("Σφάλμα κατά την ανάθεση τροφίμου");
                }
            }
        }
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο προμηθευτής ενημερώθηκε επιτυχώς'
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