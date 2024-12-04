<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $sql = "SELECT DISTINCT Onoma FROM EIDOS ORDER BY Onoma";
    $result = $db->query($sql);
    
    if (!$result) {
        throw new Exception("Σφάλμα στο ερώτημα: " . $db->error);
    }
    
    $species = [];
    while ($row = $result->fetch_assoc()) {
        $species[] = $row;
    }
    
    if (empty($species)) {
        throw new Exception('Δεν βρέθηκαν είδη στη βάση δεδομένων');
    }
    
    echo json_encode($species, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>