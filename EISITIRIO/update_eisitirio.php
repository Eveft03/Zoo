<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();

    if (!isset($_POST['kodikos'], $_POST['hmerominia_ekdoshs'])) {
        throw new Exception("Απαιτούνται τα βασικά στοιχεία του εισιτηρίου");
    }

    $db->beginTransaction();

    // Έλεγχος ημερομηνίας (όχι Κυριακή)
    if (isset($_POST['hmerominia_ekdoshs'])) {
        $date = new DateTime($_POST['hmerominia_ekdoshs']);
        if ($date->format('w') == 0) {
            throw new Exception("Δεν επιτρέπεται η έκδοση εισιτηρίων την Κυριακή");
        }
    }

    // Έλεγχος κατηγορίας και εκδηλώσεων
    if (isset($_POST['katigoria']) && $_POST['katigoria'] === 'Με εκδήλωση') {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
        $stmt->bind_param("s", $_POST['hmerominia_ekdoshs']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()['count'] === 0) {
            throw new Exception("Δεν υπάρχουν εκδηλώσεις για την επιλεγμένη ημερομηνία");
        }
    }

    $updates = [];
    $types = "";
    $values = [];

    // Δυναμική κατασκευή του query update
    foreach (['timi', 'idTamia', 'email', 'katigoria'] as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            $updates[] = "$field = ?";
            $types .= $field === 'timi' ? 'd' : 's';
            $values[] = $_POST[$field];
        }
    }

    if (!empty($updates)) {
        $sql = "UPDATE EISITIRIO SET " . implode(", ", $updates) . 
               " WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?";
        $types .= "si";
        $values[] = $_POST['kodikos'];
        $values[] = $_POST['hmerominia_ekdoshs'];

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του εισιτηρίου");
        }
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) $db->close();
}