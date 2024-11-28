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
    if (!isset($_POST['old_titlos']) || empty($_POST['old_titlos'])) {
        throw new Exception("Δεν βρέθηκε ο αρχικός τίτλος της εκδήλωσης");
    }
    if (!isset($_POST['old_hmerominia']) || empty($_POST['old_hmerominia'])) {
        throw new Exception("Δεν βρέθηκε η αρχική ημερομηνία της εκδήλωσης");
    }

    // Validate date format
    $date = date('Y-m-d', strtotime($_POST['hmerominia']));
    $old_date = date('Y-m-d', strtotime($_POST['old_hmerominia']));
    if ($date === false || $old_date === false) {
        throw new Exception("Μη έγκυρη μορφή ημερομηνίας");
    }

    // Begin transaction
    $db->beginTransaction();

    // Check if the new title/date combination already exists (except for the current event)
    if ($_POST['titlos'] !== $_POST['old_titlos'] || $date !== $old_date) {
        $stmt = $db->prepare("
            SELECT Titlos 
            FROM EKDILOSI 
            WHERE Titlos = ? AND Hmerominia = ? 
            AND (Titlos != ? OR Hmerominia != ?)
        ");
        $stmt->bind_param("ssss", $_POST['titlos'], $date, $_POST['old_titlos'], $old_date);
        $stmt->execute();
        if ($stmt->get_result()->num_rows) {
            throw new Exception("Υπάρχει ήδη εκδήλωση με αυτόν τον τίτλο για αυτή την ημερομηνία");
        }
    }

    // Update event
    $stmt = $db->prepare("
        UPDATE EKDILOSI 
        SET Titlos = ?,
            Hmerominia = ?,
            Perigrafi = ?
        WHERE Titlos = ? AND Hmerominia = ?
    ");
    
    $perigrafi = $_POST['perigrafi'] ?? null;
    $stmt->bind_param("sssss", 
        $_POST['titlos'],
        $date,
        $perigrafi,
        $_POST['old_titlos'],
        $old_date
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την ενημέρωση της εκδήλωσης");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε η εκδήλωση για ενημέρωση");
    }

    // Update related records in SYMMETEXEI if title or date changed
    if ($_POST['titlos'] !== $_POST['old_titlos'] || $date !== $old_date) {
        $stmt = $db->prepare("
            UPDATE SYMMETEXEI 
            SET Titlos = ?, Hmerominia = ?
            WHERE Titlos = ? AND Hmerominia = ?
        ");
        $stmt->bind_param("ssss", 
            $_POST['titlos'], 
            $date, 
            $_POST['old_titlos'], 
            $old_date
        );
        $stmt->execute();
    }

    // Update participating animals if provided
    if (isset($_POST['zwa']) && is_array($_POST['zwa'])) {
        // First delete all existing associations
        $stmt = $db->prepare("DELETE FROM SYMMETEXEI WHERE Titlos = ? AND Hmerominia = ?");
        $stmt->bind_param("ss", $_POST['titlos'], $date);
        $stmt->execute();

        // Then insert new associations
        $stmt = $db->prepare("INSERT INTO SYMMETEXEI (Kodikos, Titlos, Hmerominia) VALUES (?, ?, ?)");
        foreach ($_POST['zwa'] as $kodikos) {
            $stmt->bind_param("sss", $kodikos, $_POST['titlos'], $date);
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την ενημέρωση των συμμετεχόντων ζώων");
            }
        }
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Η εκδήλωση ενημερώθηκε επιτυχώς'
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