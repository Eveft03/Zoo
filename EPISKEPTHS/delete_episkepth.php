<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['Email'])) {
        throw new Exception("Δεν βρέθηκε το email του επισκέπτη");
    }

    $db->beginTransaction();

    // Έλεγχος εξαρτήσεων στο EISITIRIO
    $stmt = $db->prepare("SELECT DISTINCT e.Kodikos, e.Hmerominia_Ekdoshs 
                         FROM EISITIRIO e 
                         LEFT JOIN APAITEI a ON e.Kodikos = a.Kodikos 
                         WHERE e.Email = ?");
    $stmt->bind_param("s", $data['Email']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Διαγραφή από APAITEI και μετά από EISITIRIO
    while ($row = $result->fetch_assoc()) {
        $stmt = $db->prepare("DELETE FROM APAITEI WHERE Kodikos = ? AND Hmerominia_Ekdoshs = ?");
        $stmt->bind_param("is", $row['Kodikos'], $row['Hmerominia_Ekdoshs']);
        $stmt->execute();
    }

    $stmt = $db->prepare("DELETE FROM EISITIRIO WHERE Email = ?");
    $stmt->bind_param("s", $data['Email']);
    $stmt->execute();

    // Διαγραφή επισκέπτη
    $stmt = $db->prepare("DELETE FROM EPISKEPTIS WHERE Email = ?");
    $stmt->bind_param("s", $data['Email']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του επισκέπτη");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε ο επισκέπτης");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο επισκέπτης διαγράφηκε επιτυχώς'
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