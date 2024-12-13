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

    if (!isset($_POST['kodikos']) || !isset($_POST['hmerominia_ekdoshs'])) {
        throw new Exception("Απαιτούνται τα στοιχεία του εισιτηρίου");
    }

    // Έλεγχος αν υπάρχει το εισιτήριο
    $checkStmt = $db->prepare("SELECT 1 FROM EISITIRIO WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
    $checkStmt->bind_param("is", $_POST['kodikos'], $_POST['hmerominia_ekdoshs']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Το εισιτήριο δεν βρέθηκε");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['timi']) && $_POST['timi'] !== '') {
        if (!is_numeric($_POST['timi']) || $_POST['timi'] < 0) {
            throw new Exception("Μη έγκυρη τιμή");
        }
        $updates[] = "Timi = ?";
        $types .= "d";
        $values[] = $_POST['timi'];
    }

    if (isset($_POST['idTamia']) && $_POST['idTamia'] !== '') {
        $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
        $stmt->bind_param("i", $_POST['idTamia']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Ο ταμίας δεν υπάρχει");
        }
        $updates[] = "IDTamia = ?";
        $types .= "i";
        $values[] = $_POST['idTamia'];
    } elseif (isset($_POST['idTamia']) && $_POST['idTamia'] === '') {
        $updates[] = "IDTamia = NULL";
    }

    if (isset($_POST['email']) && !empty($_POST['email'])) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Μη έγκυρη διεύθυνση email");
        }
        $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Ο επισκέπτης δεν υπάρχει");
        }
        $updates[] = "Email = ?";
        $types .= "s";
        $values[] = $_POST['email'];
    }

    if (isset($_POST['katigoria']) && !empty($_POST['katigoria'])) {
        if (!in_array($_POST['katigoria'], ['Με εκδήλωση', 'Χωρίς εκδήλωση'])) {
            throw new Exception("Μη έγκυρη κατηγορία εισιτηρίου");
        }
        $updates[] = "Katigoria = ?";
        $types .= "s";
        $values[] = $_POST['katigoria'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->begin_transaction();

    try {
        $sql = "UPDATE EISITIRIO SET " . implode(", ", $updates) . 
               " WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?";
        $types .= "is";
        $values[] = $_POST['kodikos'];
        $values[] = $_POST['hmerominia_ekdoshs'];

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του εισιτηρίου: " . $stmt->error);
        }

        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο ενημερώθηκε επιτυχώς'], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
