<?php
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDatabase();
    $section = $_GET['section'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

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
                z.Onoma_Eidous,
                e.Katigoria,
                e.Perigrafi
            FROM ZWO z 
            JOIN EIDOS e ON z.Onoma_Eidous = e.Onoma 
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
                SELECT e.*, GROUP_CONCAT(z.Onoma) as Συμμετέχοντα_Ζώα 
                FROM EKDILOSI e 
                LEFT JOIN SYMMETEXEI s ON e.Titlos = s.Titlos AND e.Hmerominia = s.Hmerominia 
                LEFT JOIN ZWO z ON s.Kodikos = z.Kodikos 
                GROUP BY e.Titlos, e.Hmerominia 
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            break;

        case 'Εισιτήρια':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM EISITIRIO")->fetch_assoc()['count'];
            
            $stmt = $db->prepare("
                SELECT e.*, t.Onoma as Tamias_Onoma, t.Eponymo as Tamias_Eponymo,
                       ep.Onoma as Episkeptis_Onoma, ep.Eponymo as Episkeptis_Eponymo
                FROM EISITIRIO e
                LEFT JOIN TAMIAS t ON e.IDTamia = t.ID
                LEFT JOIN EPISKEPTIS ep ON e.Email = ep.Email
                ORDER BY e.Hmerominia_Ekdoshs DESC, e.Kodikos
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
                SELECT f.*, 
                       GROUP_CONCAT(z.Onoma) as Ζώα_Φροντίδας
                FROM FRONTISTIS f
                LEFT JOIN FRONTIZEI fr ON f.ID = fr.ID
                LEFT JOIN ZWO z ON fr.Kodikos = z.Kodikos
                GROUP BY f.ID
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            break;

        case 'Προμηθευτές':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM promiueftis")->fetch_assoc()['count'];
            
            $stmt = $db->prepare("
                SELECT p.*,
                       GROUP_CONCAT(t.Onoma) as Προϊόντα
                FROM promiueftis p
                LEFT JOIN TROFIMO t ON p.AFM = t.AFM_PROMITHEUTI
                GROUP BY p.AFM
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            break;

        default:
            throw new Exception("Άγνωστη ενότητα");
    }

    // Συλλογή των δεδομένων
    if(isset($result)) {
        while($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
    }

    // Προσθήκη πληροφοριών σελιδοποίησης
    $response['pagination'] = [
        'currentPage' => $page,
        'totalPages' => ceil($totalRows / $limit),
        'totalItems' => $totalRows,
        'itemsPerPage' => $limit
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>