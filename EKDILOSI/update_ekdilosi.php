<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();
    
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    if (!isset($_POST['titlos'], $_POST['old_hmerominia'])) {
        throw new Exception("Απαιτούνται τα στοιχεία της εκδήλωσης");
    }

    // Έλεγχος ύπαρξης της εκδήλωσης
    $checkStmt = $db->prepare("SELECT 1 FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $checkStmt->bind_param("ss", $_POST['titlos'], $_POST['old_hmerominia']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Η εκδήλωση δεν βρέθηκε");
    }

    $updates = [];
    $types = "";
    $values = [];

    // Έλεγχος και ενημέρωση Ημερομηνίας
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

    // Έλεγχος και ενημέρωση Ώρας
    if (isset($_POST['ora']) && !empty($_POST['ora'])) {
        $updates[] = "Ora = ?";
        $types .= "s";
        $values[] = $_POST['ora'];
    }

    // Έλεγχος και ενημέρωση Χώρου
    if (isset($_POST['xwros']) && !empty($_POST['xwros'])) {
        $updates[] = "Xwros = ?";
        $types .= "s";
        $values[] = $_POST['xwros'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->begin_transaction();

    try {
        $sql = "UPDATE EKDILOSI SET " . implode(", ", $updates) . " WHERE Titlos = ? AND Hmerominia = ?";
        $types .= "ss";
        $values[] = $_POST['titlos'];
        $values[] = $_POST['old_hmerominia'];

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση της εκδήλωσης: " . $stmt->error);
        }

        // Αν αλλάξε η ημερομηνία, ενημέρωση του πίνακα APAITEI
        if (isset($_POST['hmerominia']) && $_POST['hmerominia'] !== $_POST['old_hmerominia']) {
            $stmt = $db->prepare("UPDATE APAITEI SET Hmerominia = ? WHERE Titlos = ? AND Hmerominia = ?");
            $stmt->bind_param("sss", $_POST['hmerominia'], $_POST['titlos'], $_POST['old_hmerominia']);
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την ενημέρωση του πίνακα APAITEI: " . $stmt->error);
            }
        }

        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Η εκδήλωση ενημερώθηκε επιτυχώς'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

?>