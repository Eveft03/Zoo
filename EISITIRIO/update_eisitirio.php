<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    if (!isset($_POST['arithmos']) || empty($_POST['arithmos'])) {
        throw new Exception("Απαιτείται ο αριθμός του εισιτηρίου");
    }

    // Build update query dynamically based on provided fields
    $updates = [];
    $types = "";
    $values = [];

    if (isset($_POST['email']) && !empty($_POST['email'])) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Μη έγκυρη διεύθυνση email");
        }
        $updates[] = "Email = ?";
        $types .= "s";
        $values[] = $_POST['email'];
    }

    if (isset($_POST['imerominia']) && !empty($_POST['imerominia'])) {
        $date = DateTime::createFromFormat('Y-m-d', $_POST['imerominia']);
        if (!$date || $date->format('Y-m-d') !== $_POST['imerominia']) {
            throw new Exception("Μη έγκυρη ημερομηνία");
        }
        $updates[] = "Imerominia = ?";
        $types .= "s";
        $values[] = $_POST['imerominia'];
    }

    if (isset($_POST['ora']) && !empty($_POST['ora'])) {
        if (!preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $_POST['ora'])) {
            throw new Exception("Μη έγκυρη ώρα");
        }
        $updates[] = "Ora = ?";
        $types .= "s";
        $values[] = $_POST['ora'];
    }

    if (isset($_POST['timi']) && !empty($_POST['timi'])) {
        if (!is_numeric($_POST['timi']) || $_POST['timi'] <= 0) {
            throw new Exception("Μη έγκυρη τιμή");
        }
        $updates[] = "Timi = ?";
        $types .= "d";
        $values[] = $_POST['timi'];
    }

    if (isset($_POST['idTamia']) && !empty($_POST['idTamia'])) {
        $updates[] = "IDTamia = ?";
        $types .= "s";
        $values[] = $_POST['idTamia'];
    }

    if (empty($updates)) {
        throw new Exception("Δεν παρέχονται δεδομένα για ενημέρωση");
    }

    $db->beginTransaction();

    // Check if ticket exists
    $stmt = $db->prepare("SELECT Arithmos FROM EISITIRIO WHERE Arithmos = ?");
    $stmt->bind_param("s", $_POST['arithmos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Το εισιτήριο δεν βρέθηκε");
    }

    // If updating cashier, check if exists
    if (isset($_POST['idTamia']) && !empty($_POST['idTamia'])) {
        $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
        $stmt->bind_param("s", $_POST['idTamia']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Ο ταμίας δεν βρέθηκε");
        }
    }

    // If updating visitor email, check if exists
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $stmt = $db->prepare("SELECT Email FROM EPISKEPTIS WHERE Email = ?");
        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Ο επισκέπτης δεν βρέθηκε");
        }
    }

    // Prepare and execute update query
    $sql = "UPDATE EISITIRIO SET " . implode(", ", $updates) . " WHERE Arithmos = ?";
    $stmt = $db->prepare($sql);
    
    $types .= "s"; // Add type for Arithmos in WHERE clause
    $values[] = $_POST['arithmos'];
    
    $stmt->bind_param($types, ...$values);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση του εισιτηρίου");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν έγιναν αλλαγές στο εισιτήριο");
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Το εισιτήριο ενημερώθηκε επιτυχώς'
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