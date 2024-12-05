<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();

    if (!isset($_POST['kodikos'], $_POST['hmerominia_ekdoshs'])) {
        throw new Exception("Απαιτούνται τα στοιχεία του εισιτηρίου");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['timi']) && !empty($_POST['timi'])) {
        if (!is_numeric($_POST['timi']) || $_POST['timi'] <= 0) {
            throw new Exception("Μη έγκυρη τιμή");
        }
        $updates[] = "Timi = ?";
        $types .= "d";
        $values[] = $_POST['timi'];
    }

    if (isset($_POST['email']) && !empty($_POST['email'])) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Μη έγκυρη διεύθυνση email");
        }
        $updates[] = "Email = ?";
        $types .= "s";
        $values[] = $_POST['email'];
    }

    if (isset($_POST['idTamia']) && !empty($_POST['idTamia'])) {
        $updates[] = "IDTamia = ?";
        $types .= "s";
        $values[] = $_POST['idTamia'];
    }

    if (isset($_POST['katigoria']) && !empty($_POST['katigoria'])) {
        if ($_POST['katigoria'] === 'Με εκδήλωση') {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
            $stmt->bind_param("s", $_POST['hmerominia_ekdoshs']);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()['count'] === 0) {
                throw new Exception("Δεν υπάρχουν εκδηλώσεις για την επιλεγμένη ημερομηνία");
            }
        }
        $updates[] = "Katigoria = ?";
        $types .= "s";
        $values[] = $_POST['katigoria'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    $sql = "UPDATE EISITIRIO SET " . implode(", ", $updates) . 
           " WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?";
    $types .= "ss";
    $values[] = $_POST['kodikos'];
    $values[] = $_POST['hmerominia_ekdoshs'];

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του εισιτηρίου");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>