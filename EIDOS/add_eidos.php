<?php
require_once '../db_connection.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
   $db = getDatabase();
   
   // Validate required fields
   if (!isset($_POST['onoma']) || empty(trim($_POST['onoma']))) {
       throw new Exception("Το όνομα είναι υποχρεωτικό");
   }
   if (!isset($_POST['katigoria']) || empty(trim($_POST['katigoria']))) {
       throw new Exception("Η κατηγορία είναι υποχρεωτική");
   }
   if (!isset($_POST['perigrafi']) || empty(trim($_POST['perigrafi']))) {
       throw new Exception("Η περιγραφή είναι υποχρεωτική");
   }

   $db->beginTransaction();

   // Check for duplicate species
   $stmt = $db->prepare("SELECT Onoma FROM EIDOS WHERE Onoma = ?");
   $onoma_check = $_POST['onoma'];
   $stmt->bind_param("s", $onoma_check);
   $stmt->execute();
   if ($stmt->get_result()->num_rows > 0) {
       throw new Exception("Το είδος υπάρχει ήδη");
   }

   // Insert species
   $stmt = $db->prepare("INSERT INTO EIDOS (Onoma, Katigoria, Perigrafi) VALUES (?, ?, ?)");
   
   $onoma = htmlspecialchars(trim($_POST['onoma']));
   $katigoria = htmlspecialchars(trim($_POST['katigoria']));
   $perigrafi = htmlspecialchars(trim($_POST['perigrafi']));
   
   $stmt->bind_param("sss", $onoma, $katigoria, $perigrafi);
   
   if (!$stmt->execute()) {
       throw new Exception("Σφάλμα κατά την εισαγωγή του είδους");
   }

   // Handle food associations if provided
   if (isset($_POST['trofima']) && is_array($_POST['trofima'])) {
       $stmt = $db->prepare("INSERT INTO TREFETAI (Eidos_onoma, Trofimo_kodikos) VALUES (?, ?)");
       
       foreach ($_POST['trofima'] as $trofimo_kodikos) {
           // Verify food exists
           $checkStmt = $db->prepare("SELECT Kodikos FROM TROFIMO WHERE Kodikos = ?");
           $kodikos_check = $trofimo_kodikos;
           $checkStmt->bind_param("s", $kodikos_check);
           $checkStmt->execute();
           if ($checkStmt->get_result()->num_rows === 0) {
               throw new Exception("Το τρόφιμο με κωδικό $trofimo_kodikos δεν υπάρχει");
           }

           $stmt->bind_param("ss", $onoma, $trofimo_kodikos);
           if (!$stmt->execute()) {
               throw new Exception("Σφάλμα κατά την ανάθεση τροφίμου");
           }
       }
   }

   $db->commit();
   echo json_encode(['status' => 'success', 'message' => 'Το είδος προστέθηκε επιτυχώς']);

} catch (Exception $e) {
   if (isset($db)) $db->rollback();
   http_response_code(400);
   echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>