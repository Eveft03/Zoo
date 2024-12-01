<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
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

    // Validate day (Monday/Wednesday/Friday)
    $date = new DateTime($_POST['hmerominia']);
    $dayOfWeek = $date->format('N'); // 1-7 (Monday-Sunday)
    if (!in_array($dayOfWeek, [1, 3, 5])) {
        throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
    }

    $db->beginTransaction();

    // Check for duplicate events on same date
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
    $stmt->bind_param("s", $_POST['hmerominia']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['count'] >= 2) {
        throw new Exception("Έχει συμπληρωθεί ο μέγιστος αριθμός εκδηλώσεων (2) για την επιλεγμένη ημερομηνία");
    }

    // Check for duplicate event at same time
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ? AND Ora = ?");
    $stmt->bind_param("ss", $_POST['hmerominia'], $_POST['ora']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['count'] > 0) {
        throw new Exception("Υπάρχει ήδη εκδήλωση την ίδια ώρα");
    }

    // Insert event
    $stmt = $db->prepare("
        INSERT INTO EKDILOSI (Titlos, Hmerominia, Ora, Xwros)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssss", 
        htmlspecialchars($_POST['titlos']),
        $_POST['hmerominia'],
        $_POST['ora'],
        htmlspecialchars($_POST['xwros'])
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή της εκδήλωσης");
    }

    // Handle animal participants if provided
    if (isset($_POST['zwa']) && is_array($_POST['zwa'])) {
        $stmt = $db->prepare("
            INSERT INTO SYMMETEXEI (Titlos, Hmerominia, Kodikos)
            VALUES (?, ?, ?)
        ");
        
        foreach ($_POST['zwa'] as $kodikos) {
            // Validate that animal exists
            $checkStmt = $db->prepare("SELECT Kodikos FROM ZWO WHERE Kodikos = ?");
            $checkStmt->bind_param("s", $kodikos);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows === 0) {
                throw new Exception("Το ζώο με κωδικό $kodikos δεν υπάρχει");
            }

            $stmt->bind_param("sss", 
                $_POST['titlos'],
                $_POST['hmerominia'],
                $kodikos
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την προσθήκη ζώου στην εκδήλωση");
            }
        }
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