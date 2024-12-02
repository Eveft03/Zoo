<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate required fields
    $required_fields = ['afm', 'onoma', 'eponymo', 'tilefono', 'dieuthinsi'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Validate AFM (9 digits)
    if (!preg_match('/^\d{9}$/', $_POST['afm'])) {
        throw new Exception("Το ΑΦΜ πρέπει να αποτελείται από 9 ψηφία");
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $_POST['tilefono'])) {
        throw new Exception("Μη έγκυρος αριθμός τηλεφώνου");
    }

    $db->beginTransaction();

    // Check if AFM already exists
    $stmt = $db->prepare("SELECT AFM FROM promitheutis WHERE AFM = ?");
    $stmt->bind_param("s", $_POST['afm']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Το ΑΦΜ προμηθευτή υπάρχει ήδη");
    }

    // Insert supplier
    $stmt = $db->prepare("
        INSERT INTO promitheutis (AFM, Onoma, Eponymo, Tilefono, Dieuthinsi) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("sssss", 
        $_POST['afm'],
        $_POST['onoma'],
        $_POST['eponymo'],
        $_POST['tilefono'],
        $_POST['dieuthinsi']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του προμηθευτή");
    }

    // Handle food items if provided
    if (isset($_POST['trofima']) && is_array($_POST['trofima'])) {
        $stmt = $db->prepare("UPDATE TROFIMO SET AFM_PROMITHEUTI = ? WHERE Onoma = ?");
        foreach ($_POST['trofima'] as $trofimo) {
            $stmt->bind_param("ss", $_POST['afm'], $trofimo);
            if (!$stmt->execute()) {
                throw new Exception("Σφάλμα κατά την ανάθεση τροφίμου");
            }
        }
    }

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Ο προμηθευτής καταχωρήθηκε επιτυχώς'
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