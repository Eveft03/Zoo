<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();

    // Έλεγχος αν υπάρχει σύνδεση με τη βάση
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    // Επιβεβαίωση υποχρεωτικών πεδίων
    $required_fields = ['titlos', 'hmerominia', 'ora', 'xwros'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος ημέρας (Δευτέρα/Τετάρτη/Παρασκευή)
    $date = new DateTime($_POST['hmerominia']);
    $dayOfWeek = $date->format('N');
    if (!in_array($dayOfWeek, [1, 3, 5])) {
        throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
    }

    // Έναρξη συναλλαγής
    $db->begin_transaction();

    // Έλεγχος μέγιστου αριθμού εκδηλώσεων για την ημέρα
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
    $stmt->bind_param("s", $_POST['hmerominia']);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['count'] >= 2) {
        throw new Exception("Έχει συμπληρωθεί ο μέγιστος αριθμός εκδηλώσεων για την επιλεγμένη ημερομηνία");
    }

    // Έλεγχος αν υπάρχει ήδη εκδήλωση με τον ίδιο τίτλο στην ίδια ημερομηνία
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $_POST['titlos'], $_POST['hmerominia']);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['count'] > 0) {
        throw new Exception("Υπάρχει ήδη εκδήλωση με αυτόν τον τίτλο για την επιλεγμένη ημερομηνία");
    }

    // Εισαγωγή εκδήλωσης
    $stmt = $db->prepare("
        INSERT INTO EKDILOSI (Titlos, Hmerominia, Ora, Xwros)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", 
        $_POST['titlos'],
        $_POST['hmerominia'],
        $_POST['ora'],
        $_POST['xwros']
    );
   
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή της εκδήλωσης: " . $stmt->error);
    }

    $db->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Η εκδήλωση προστέθηκε επιτυχώς'
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