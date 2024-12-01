<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Έλεγχος απαιτούμενων πεδίων
    $required = ['kodikos', 'email', 'hmerominia_ekdoshs', 'timi', 'idTamia', 'katigoria'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος κωδικού εισιτηρίου
    if (!preg_match('/^[0-9]{5}$/', $_POST['kodikos'])) {
        throw new Exception("Ο κωδικός πρέπει να είναι 5ψήφιος αριθμός");
    }

    // Έλεγχος email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Μη έγκυρη διεύθυνση email");
    }

    // Έλεγχος ημερομηνίας (όχι Κυριακή)
    $date = new DateTime($_POST['hmerominia_ekdoshs']);
    if ($date->format('w') == 0) { // 0 = Κυριακή
        throw new Exception("Δεν επιτρέπεται η έκδοση εισιτηρίων την Κυριακή");
    }

    // Έλεγχος κατηγορίας και εκδηλώσεων
    if ($_POST['katigoria'] === 'Με εκδήλωση') {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
        $stmt->bind_param("s", $_POST['hmerominia_ekdoshs']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result['count'] === 0) {
            throw new Exception("Δεν υπάρχουν εκδηλώσεις για την επιλεγμένη ημερομηνία");
        }
    }

    $db->beginTransaction();

    // Υπόλοιπος κώδικας εισαγωγής...
    
    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) $db->close();
}