// update_eisitirio.php
<?php
require_once 'db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['arithmos'])) {
        throw new Exception("Απαιτείται ο αριθμός του εισιτηρίου");
    }

    $updates = [];
    $types = "";
    $values = [];

    if (isset($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Μη έγκυρη διεύθυνση email");
        }
        $updates[] = "Email = ?";
        $types .= "s";
        $values[] = $data['email'];
    }

    if (isset($data['imerominia'])) {
        $updates[] = "Imerominia = ?";
        $types .= "s";
        $values[] = $data['imerominia'];
    }

    if (isset($data['ora'])) {
        if (!preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $data['ora'])) {
            throw new Exception("Μη έγκυρη ώρα");
        }
        $updates[] = "Ora = ?";
        $types .= "s";
        $values[] = $data['ora'];
    }

    if (isset($data['timi'])) {
        if (!is_numeric($data['timi']) || $data['timi'] <= 0) {
            throw new Exception("Μη έγκυρη τιμή");
        }
        $updates[] = "Timi = ?";
        $types .= "d";
        $values[] = $data['timi'];
    }

    if (isset($data['idTamia'])) {
        $updates[] = "IDTamia = ?";
        $types .= "s";
        $values[] = $data['idTamia'];
    }

    $db->beginTransaction();

    if (!empty($updates)) {
        $sql = "UPDATE EISITIRIO SET " . implode(", ", $updates) . " WHERE Arithmos = ?";
        $types .= "s";
        $values[] = $data['arithmos'];
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception("Σφάλμα κατά την ενημέρωση του εισιτηρίου");
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("Το εισιτήριο δεν βρέθηκε");
        }
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το εισιτήριο ενημερώθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>