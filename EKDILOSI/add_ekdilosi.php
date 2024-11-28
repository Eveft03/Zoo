<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['titlos']) || empty($_POST['titlos'])) {
        throw new Exception("Ο τίτλος είναι υποχρεωτικός");
    }
    if (!isset($_POST['hmerominia']) || empty($_POST['hmerominia'])) {
        throw new Exception("Η ημερομηνία είναι υποχρεωτική");
    }

    // Validate date format
    $date = date('Y-m-d', strtotime($_POST['hmerominia']));
    if ($date === false) {
        throw new Exception("Μη έγκυρη μορφή ημερομηνίας");
    }

    // Begin transaction
    $db->beginTransaction();

    // Check if event already exists
    $stmt = $db->prepare("SELECT Titlos FROM EKDILOSI WHERE Titlos = ? AND Hmerominia = ?");
    $stmt->bind_param("ss", $_POST['titlos'], $date);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) {
        throw new Exception("Η εκδήλωση υπάρχει ήδη για αυτή την ημερομηνία");
    }

    // Insert new event
    $stmt = $db->prepare("
        INSERT INTO EKDILOSI (Titlos, Hmerominia, Perigrafi) 
        VALUES (?, ?, ?)
    ");
    
    $perigrafi = $_POST['perigrafi'] ?? null;
    $stmt->bind_param("sss", 
        $_POST['titlos'],
        $date,
        $perigrafi
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή της εκδήλωσης");
    }

    // If animal IDs are provided, add them to SYMMETEXEI
    if (isset($_POST['zwa']) && is_array($_POST['zwa'])) {
        $stmt = $db->prepare("INSERT INTO SYMMETEXEI (Kodikos, Titlos, Hmerominia) VALUES (?, ?, ?)");
        
        foreach ($_POST['zwa'] as $kodikos) {
            $stmt->bind_param("sss", $kodikos, $_POST['titlos'], $date);
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την προσθήκη ζώου στην εκδήλωση");
            }
        }
    }

    // Commit transaction
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