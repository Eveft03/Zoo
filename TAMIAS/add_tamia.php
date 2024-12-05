
<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    $required_fields = ['id', 'onoma', 'eponymo'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    if (!is_numeric($_POST['id']) || $_POST['id'] <= 0) {
        throw new Exception("Το ID πρέπει να είναι θετικός ακέραιος αριθμός");
    }

    $db->beginTransaction();

    $stmt = $db->prepare("SELECT ID FROM TAMIAS WHERE ID = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ID ταμία υπάρχει ήδη");
    }

    $stmt = $db->prepare("
        INSERT INTO TAMIAS (ID, Onoma, Eponymo)
        VALUES (?, ?, ?)
    ");
    
    $stmt->bind_param("iss", 
        $_POST['id'],
        htmlspecialchars($_POST['onoma']),
        htmlspecialchars($_POST['eponymo'])
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ταμία");
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Ο ταμίας προστέθηκε επιτυχώς']);

} catch (Exception $e) {
    if (isset($db)) $db->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>