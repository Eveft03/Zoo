<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);
mb_internal_encoding('UTF-8');

try {
   $db = getDatabase();
   $section = $_GET['section'] ?? '';
   $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
   $limit = 10;
   $offset = ($page - 1) * $limit;

   error_log("Section: " . $section);
   error_log("Page: " . $page);

   $response = [
       'status' => 'success',
       'data' => [],
       'pagination' => [],
       'message' => ''
   ];

   switch($section) { 

   case 'Ζώα':
       $totalRows = $db->query("SELECT COUNT(*) as count FROM ZWO")->fetch_assoc()['count'];
       
       $stmt = $db->prepare("
           SELECT 
               z.Kodikos,
               z.Onoma,
               z.Etos_Genesis,
               z.Onoma_Eidous
           FROM ZWO z
           ORDER BY z.Kodikos
           LIMIT ? OFFSET ?
       ");
       
       $stmt->bind_param("ii", $limit, $offset);
       $stmt->execute();
       $result = $stmt->get_result();
       break;

       case 'Είδη':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM EIDOS")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("SELECT * FROM EIDOS LIMIT ? OFFSET ?");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       case 'Εκδηλώσεις':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM EKDILOSI")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("
               SELECT Titlos, Hmerominia, Ora, Xwros
               FROM EKDILOSI 
               ORDER BY Hmerominia DESC, Ora
               LIMIT ? OFFSET ?
           ");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;
           
       case 'Εισιτήρια':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM EISITIRIO")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("
               SELECT * FROM EISITIRIO 
               ORDER BY Hmerominia_Ekdoshs DESC, Kodikos
               LIMIT ? OFFSET ?
           ");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       case 'Ταμίες':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM TAMIAS")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("SELECT * FROM TAMIAS LIMIT ? OFFSET ?");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       case 'Επισκέπτες':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM EPISKEPTIS")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("SELECT * FROM EPISKEPTIS LIMIT ? OFFSET ?");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       case 'Φροντιστές':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM FRONTISTIS")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("
               SELECT f.ID, f.Onoma, f.Eponymo
               FROM FRONTISTIS f
               ORDER BY f.ID
               LIMIT ? OFFSET ?
           ");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       case 'Προμηθευτές':
           $totalRows = $db->query("SELECT COUNT(*) as count FROM PROMITHEUTIS")->fetch_assoc()['count'];
           
           $stmt = $db->prepare("
               SELECT p.AFM, p.Onoma, p.Thlefono 
               FROM PROMITHEUTIS p
               LIMIT ? OFFSET ?
           ");
           $stmt->bind_param("ii", $limit, $offset);
           $stmt->execute();
           $result = $stmt->get_result();
           break;

       default:
           throw new Exception("Άγνωστη ενότητα");
   }

   if(isset($result)) {
       while($row = $result->fetch_assoc()) {
           $response['data'][] = $row;
       }
   }

   $response['pagination'] = [
       'currentPage' => $page,
       'totalPages' => ceil($totalRows / $limit),
       'totalItems' => $totalRows,
       'itemsPerPage' => $limit
   ];

   error_log("Response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
   echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
   error_log("Error: " . $e->getMessage());
   http_response_code(500);
   echo json_encode([
       'status' => 'error',
       'message' => $e->getMessage()
   ], JSON_UNESCAPED_UNICODE);
}