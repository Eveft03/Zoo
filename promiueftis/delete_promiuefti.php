<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['AFM'])) {
        throw new Exception("Δεν βρέθηκε το ΑΦΜ του προμηθευτή");
    }

    $db->beginTransaction();

    // Αντί να ελέγξουμε απλά αν υπάρχουν τρόφιμα, παίρνουμε τη λίστα τους
    $stmt = $db->prepare("
        SELECT t.Onoma, t.Kodikos, t.Timi_kg, t.Posothta 
        FROM TROFIMO t 
        WHERE t.AFM_PROMITHEUTI = ?
    ");
    $stmt->bind_param("s", $data['AFM']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $trofima = array();
        while ($row = $result->fetch_assoc()) {
            $trofima[] = $row['Onoma'];
        }
        throw new Exception("Ο προμηθευτής δεν μπορεί να διαγραφεί γιατί είναι υπεύθυνος για τα τρόφιμα: " . implode(", ", $trofima));
    }

    // Διαγραφή προμηθευτή
    $stmt = $db->prepare("DELETE FROM PROMITHEUTIS WHERE AFM = ?");
    $stmt->bind_param("s", $data['AFM']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του προμηθευτή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε ο προμηθευτής");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο προμηθευτής διαγράφηκε επιτυχώς'
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