<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    
    // Validate input
    if (!isset($_POST['kodikos']) || empty($_POST['kodikos'])) {
        throw new Exception("Ο κωδικός είναι υποχρεωτικός");
    }
    if (!isset($_POST['onoma']) || empty($_POST['onoma'])) {
        throw new Exception("Το όνομα είναι υποχρεωτικό");
    }
    if (!isset($_POST['etos_genesis']) || empty($_POST['etos_genesis'])) {
        throw new Exception("Το έτος γέννησης είναι υποχρεωτικό");
    }
    if (!isset($_POST['onoma_eidous']) || empty($_POST['onoma_eidous'])) {
        throw new Exception("Το είδος είναι υποχρεωτικό");
    }

    // Αλλαγή pattern validation
if (!preg_match('/^Z\d{6}$/', $_POST['kodikos'])) {
    throw new Exception("Ο κωδικός πρέπει να έχει τη μορφή 'Z' ακολουθούμενο από 6 ψηφία");
}

    // Validate year
    $year = (int)$_POST['etos_genesis'];
    $current_year = date('Y');
    if ($year < 1900 || $year > $current_year) {
        throw new Exception("Μη έγκυρο έτος γέννησης");
    }

    // Begin transaction
    $db->beginTransaction();

    // Check if species exists
    $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
    $stmt->bind_param("s", $_POST['onoma_eidous']);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        throw new Exception("Το είδος δεν υπάρχει");
    }

    // Check if animal code already exists
    $stmt = $db->prepare("SELECT Kodikos FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $_POST['kodikos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) {
        throw new Exception("Ο κωδικός ζώου υπάρχει ήδη");
    }

    // Insert new animal
    $stmt = $db->prepare("
        INSERT INTO ZWO (Kodikos, Onoma, Etos_Genesis, Onoma_Eidous) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssis", 
        $_POST['kodikos'],
        $_POST['onoma'],
        $year,
        $_POST['onoma_eidous']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ζώου");
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Το ζώο προστέθηκε επιτυχώς'
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