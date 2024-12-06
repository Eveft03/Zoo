// add_promitheutis.php
<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $required_fields = ['afm', 'onoma', 'thlefono'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!preg_match('/^\d{9}$/', $_POST['afm'])) {
        throw new Exception("Το ΑΦΜ πρέπει να αποτελείται από 9 ψηφία");
    }

    if (!preg_match('/^\d{10}$/', $_POST['thlefono'])) {
        throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT AFM FROM PROMITHEUTIS WHERE AFM = ?");
    $stmt->bind_param("s", $_POST['afm']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ΑΦΜ υπάρχει ήδη");
    }

    $stmt = $db->prepare("
        INSERT INTO PROMITHEUTIS (AFM, Onoma, Thlefono)
        VALUES (?, ?, ?)
    ");
    
    $stmt->bind_param("sss", 
        $_POST['afm'],
        htmlspecialchars($_POST['onoma']),
        $_POST['thlefono']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του προμηθευτή");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο προμηθευτής προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>