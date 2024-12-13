<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
    $db = getDatabase();

    // Έλεγχος αν υπάρχει σύνδεση με τη βάση
    if (!$db) {
        throw new Exception("Πρόβλημα σύνδεσης με τη βάση δεδομένων");
    }

    // Επιβεβαίωση πεδίων
    $required_fields = ['kodikos', 'onoma', 'etos_genesis', 'onoma_eidous'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
        }
    }

    // Έλεγχος μορφής κωδικού (π.χ. Z000001)
    if (!preg_match('/^[Zz]\d{6}$/', $_POST['kodikos'])) {
        throw new Exception("Ο κωδικός πρέπει να έχει τη μορφή 'Z' ακολουθούμενο από 6 ψηφία");
    }

    // Έλεγχος έτους γέννησης
    $year = (int)$_POST['etos_genesis'];
    $current_year = date('Y');
    if ($year < 1900 || $year > $current_year) {
        throw new Exception("Μη έγκυρο έτος γέννησης");
    }

    // Έναρξη συναλλαγής
    $db->begin_transaction();

    // Έλεγχος για διπλότυπο κωδικό
    $stmt = $db->prepare("SELECT Kodikos FROM ZWO WHERE Kodikos = ?");
    $stmt->bind_param("s", $_POST['kodikos']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Ο κωδικός ζώου υπάρχει ήδη");
    }

    // Έλεγχος αν υπάρχει το είδος
    $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
    $stmt->bind_param("s", $_POST['onoma_eidous']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Το είδος δεν υπάρχει");
    }

    // Εισαγωγή ζώου
    $stmt = $db->prepare("
        INSERT INTO ZWO (Kodikos, Onoma, Etos_Genesis, Onoma_Eidous)
        VALUES (?, ?, ?, ?)
    ");
    $onoma_clean = htmlspecialchars($_POST['onoma'], ENT_QUOTES, 'UTF-8');
    $stmt->bind_param("ssis", 
        $_POST['kodikos'],
        $onoma_clean,
        $year,
        $_POST['onoma_eidous']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Σφάλμα κατά την εισαγωγή του ζώου: " . $stmt->error);
    }

    $db->commit();
    echo json_encode(['status' => 'success', 'message' => 'Το ζώο προστέθηκε επιτυχώς'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

?>
