<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    if (!isset($_POST['titlos'], $_POST['old_hmerominia'])) {
        throw new Exception("Απαιτούνται τα στοιχεία της εκδήλωσης");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['hmerominia']) && !empty($_POST['hmerominia'])) {
        $date = new DateTime($_POST['hmerominia']);
        $dayOfWeek = $date->format('N');
        if (!in_array($dayOfWeek, [1, 3, 5])) {
            throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ? AND Hmerominia != ?");
        $stmt->bind_param("ss", $_POST['hmerominia'], $_POST['old_hmerominia']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] >= 2) {
            throw new Exception("Έχει συμπληρωθεί ο μέγιστος αριθμός εκδηλώσεων για την επιλεγμένη ημερομηνία");
        }
        
        $updates[] = "Hmerominia = ?";
        $types .= "s";
        $values[] = $_POST['hmerominia'];
    }

    if (isset($_POST['ora']) && !empty($_POST['ora'])) {
        $updates[] = "Ora = ?";
        $types .= "s";
        $values[] = $_POST['ora'];
    }

    if (isset($_POST['xwros']) && !empty($_POST['xwros'])) {
        $updates[] = "Xwros = ?";
        $types .= "s";
        $values[] = $_POST['xwros'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE EKDILOSI SET " . implode(", ", $updates) . " WHERE Titlos = ? AND Hmerominia = ?";
    $types .= "ss";
    $values[] = $_POST['titlos'];
    $values[] = $_POST['old_hmerominia'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση της εκδήλωσης");
    }

    // Update APAITEI table if date changed
    if (isset($_POST['hmerominia']) && $_POST['hmerominia'] !== $_POST['old_hmerominia']) {
        $stmt = $db->prepare("UPDATE APAITEI SET Hmerominia = ? WHERE Titlos = ? AND Hmerominia = ?");
        $stmt->bind_param("sss", $_POST['hmerominia'], $_POST['titlos'], $_POST['old_hmerominia']);
        $stmt->execute();
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Η εκδήλωση ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>