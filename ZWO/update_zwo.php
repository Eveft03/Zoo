<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();

    // Λήψη δεδομένων JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception("Μη έγκυρα δεδομένα JSON");
    }

    // Έλεγχος δεδομένων
    if (!isset($data['kodikos']) || empty($data['kodikos'])) {
        throw new Exception("Ο κωδικός είναι υποχρεωτικός");
    }
    if (!isset($data['onoma']) || empty($data['onoma'])) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
    }
    if (!isset($data['etos_genesis']) || empty($data['etos_genesis'])) {
        throw new Exception("Το έτος γέννησης είναι υποχρεωτικό");
    }
    if (!isset($data['onoma_eidous']) || empty($data['onoma_eidous'])) {
        throw new Exception("Το είδος είναι υποχρεωτικό");
    }

    // Έλεγχος έτους
    $year = (int)$data['etos_genesis'];
    $current_year = date('Y');
    if ($year < 1900 || $year > $current_year) {
        throw new Exception("Μη έγκυρο έτος γέννησης");
    }

    // Έναρξη συναλλαγής
    $db->beginTransaction();

    // Έλεγχος αν υπάρχει το είδος
    $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
    $stmt->bind_param("s", $data['onoma_eidous']);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        throw new Exception("Το είδος δεν υπάρχει");
    }

    // Ενημέρωση ζώου
    $stmt = $db->prepare("
        UPDATE ZWO 
        SET Onoma = ?, Etos_Genesis = ?, Onoma_Eidous = ? 
        WHERE Kodikos = ?
    ");
    $stmt->bind_param("siss", 
        $data['onoma'], 
        $year, 
        $data['onoma_eidous'], 
        $data['kodikos']
    );

    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα SQL: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Δεν βρέθηκε το ζώο για ενημέρωση");
    }

    // Ολοκλήρωση συναλλαγής
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Το ζώο ενημερώθηκε επιτυχώς'
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
