<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Έλεγχος απαιτούμενων πεδίων
    if (!isset($_POST['titlos']) || empty($_POST['titlos'])) {
        throw new Exception("Ο τίτλος είναι υποχρεωτικός");
    }
    if (!isset($_POST['hmerominia']) || empty($_POST['hmerominia'])) {
        throw new Exception("Η ημερομηνία είναι υποχρεωτική");
    }
    if (!isset($_POST['ora']) || empty($_POST['ora'])) {
        throw new Exception("Η ώρα είναι υποχρεωτική");
    }
    if (!isset($_POST['xwros']) || empty($_POST['xwros'])) {
        throw new Exception("Ο χώρος είναι υποχρεωτικός");
    }

    // Έλεγχος ημέρας (Δευτέρα/Τετάρτη/Παρασκευή)
    $date = new DateTime($_POST['hmerominia']);
    $dayOfWeek = $date->format('N'); // 1-7 (Δευτέρα-Κυριακή)
    if (!in_array($dayOfWeek, [1, 3, 5])) {
        throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
    }

    // Έλεγχος μέγιστου αριθμού εκδηλώσεων
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
    $stmt->bind_param("s", $_POST['hmerominia']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['count'] >= 2) {
        throw new Exception("Έχει συμπληρωθεί ο μέγιστος αριθμός εκδηλώσεων (2) για την επιλεγμένη ημερομηνία");
    }

    $db->beginTransaction();

    // Υπόλοιπος κώδικας εισαγωγής...

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Η εκδήλωση προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) $db->close();
}