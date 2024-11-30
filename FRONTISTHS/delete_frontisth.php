<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ID'])) {
        throw new Exception("Δεν βρέθηκε το ID του φροντιστή");
    }

    $db->beginTransaction();

    // Έλεγχος και λήψη λίστας ζώων που φροντίζει
    $stmt = $db->prepare("
        SELECT z.Onoma, z.Kodikos 
        FROM ZWO z 
        JOIN FRONTIZEI f ON z.Kodikos = f.Kodikos 
        WHERE f.ID = ?
    ");
    $stmt->bind_param("i", $data['ID']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $zwa = array();
        while ($row = $result->fetch_assoc()) {
            $zwa[] = $row['Onoma'];
        }
        throw new Exception("Ο φροντιστής δεν μπορεί να διαγραφεί γιατί φροντίζει τα ζώα: " . implode(", ", $zwa));
    }

    // Διαγραφή φροντιστή
    $stmt = $db->prepare("DELETE FROM FRONTISTIS WHERE ID = ?");
    $stmt->bind_param("i", $data['ID']);
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά τη διαγραφή του φροντιστή");
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε ο φροντιστής");
    }

    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Ο φροντιστής διαγράφηκε επιτυχώς'
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