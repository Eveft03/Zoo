<?php
require_once '../db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
   $db = getDatabase();
   
   // Validate required fields
   $required_fields = ['titlos', 'hmerominia', 'ora', 'xwros'];
   foreach ($required_fields as $field) {
       if (!isset($_POST[$field]) || empty($_POST[$field])) {
           throw new Exception("Το πεδίο $field είναι υποχρεωτικό");
       }
   }

   // Validate day (Monday/Wednesday/Friday)
   $date = new DateTime($_POST['hmerominia']);
   $dayOfWeek = $date->format('N');
   if (!in_array($dayOfWeek, [1, 3, 5])) {
       throw new Exception("Οι εκδηλώσεις επιτρέπονται μόνο Δευτέρα, Τετάρτη και Παρασκευή");
   }

   $db->beginTransaction();

   // Check for duplicate events on same date
   $stmt = $db->prepare("SELECT COUNT(*) as count FROM EKDILOSI WHERE Hmerominia = ?");
   $stmt->bind_param("s", $_POST['hmerominia']);
   $stmt->execute();
   if ($stmt->get_result()->fetch_assoc()['count'] >= 2) {
       throw new Exception("Έχει συμπληρωθεί ο μέγιστος αριθμός εκδηλώσεων για την επιλεγμένη ημερομηνία");
   }

   // Insert event
   $stmt = $db->prepare("
       INSERT INTO EKDILOSI (Titlos, Hmerominia, Ora, Xwros)
       VALUES (?, ?, ?, ?)
   ");
   
   $stmt->bind_param("ssss", 
       $_POST['titlos'],
       $_POST['hmerominia'],
       $_POST['ora'],
       $_POST['xwros']
   );
   
   if (!$stmt->execute()) {
       throw new Exception("Σφάλμα κατά την εισαγωγή της εκδήλωσης");
   }

   $db->commit();
   echo json_encode([
       'status' => 'success',
       'message' => 'Η εκδήλωση προστέθηκε επιτυχώς'
   ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
   if (isset($db)) $db->rollback();
   http_response_code(400);
   echo json_encode([
       'status' => 'error',
       'message' => $e->getMessage()
   ], JSON_UNESCAPED_UNICODE);
}
?>