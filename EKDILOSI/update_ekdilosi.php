<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Βασικοί έλεγχοι
    if (!isset($_POST['titlos'], $_POST['hmerominia'], $_POST['ora'], $_POST['xwros'])) {
        throw new Exception("Λείπουν απαραίτητα πεδία");
    }
    if (!isset($_POST['old_titlos'], $_POST['old_hmerominia'])) {
        throw new Exception("Λείπουν στοιχεία παλιάς εκδήλωσης");
    }

    // Έλεγχος ημέρας
    $date = new DateTime($_POST['hmerominia']);
    $dayOfWeek = $date->format('N');
    if (!in_array($dayOfWeek, [1, 3, 5])) {
        throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
    }

    $db->beginTransaction();

    // Έλεγχος διαθεσιμότητας ημερομηνίας
    if ($_POST['hmerominia'] !== $_POST['old_hmerominia']) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM EKDILOSI 
            WHERE Hmerominia = ? AND Hmerominia != ?
        ");
        $stmt->bind_param("ss", $_POST['hmerominia'], $_POST['old_hmerominia']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] >= 2) {
            throw new Exception("Υπάρχουν ήδη 2 εκδηλώσεις την επιλεγμένη ημερομηνία");
        }
    }

    // Ενημέρωση εκδήλωσης
    $stmt = $db->prepare("
        UPDATE EKDILOSI 
        SET Titlos = ?, Hmerominia = ?, Ora = ?, Xwros = ?
        WHERE Titlos = ? AND Hmerominia = ?
    ");
    
    $stmt->bind_param("ssssss", 
        $_POST['titlos'],
        $_POST['hmerominia'],
        $_POST['ora'],
        $_POST['xwros'],
        $_POST['old_titlos'],
        $_POST['old_hmerominia']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση της εκδήλωσης");
    }

    // Ενημέρωση σχετικών εγγραφών
    if ($_POST['titlos'] !== $_POST['old_titlos'] || $_POST['hmerominia'] !== $_POST['old_hmerominia']) {
        // Ενημέρωση SYMMETEXEI
        $stmt = $db->prepare("
            UPDATE SYMMETEXEI 
            SET Titlos = ?, Hmerominia = ?
            WHERE Titlos = ? AND Hmerominia = ?
        ");
        $stmt->bind_param("ssss", 
            $_POST['titlos'], 
            $_POST['hmerominia'], 
            $_POST['old_titlos'], 
            $_POST['old_hmerominia']
        );
        $stmt->execute();

        // Ενημέρωση APAITEI
        $stmt = $db->prepare("
            UPDATE APAITEI 
            SET Titlos = ?, Hmerominia = ?
            WHERE Titlos = ? AND Hmerominia = ?
        ");
        $stmt->bind_param("ssss", 
            $_POST['titlos'], 
            $_POST['hmerominia'], 
            $_POST['old_titlos'], 
            $_POST['old_hmerominia']
        );
        $stmt->execute();
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Η εκδήλωση ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) $db->close();
}