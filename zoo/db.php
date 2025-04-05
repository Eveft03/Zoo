<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'db_connection.php';
    $db = getDatabase();
    if (!$db) {
        throw new Exception("Database connection error");
    }

    $section = isset($_GET['section']) ? htmlspecialchars($_GET['section'], ENT_QUOTES, 'UTF-8') : '';
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
                SELECT z.Kodikos, z.Onoma, z.Etos_Genesis, z.Onoma_Eidous
                FROM ZWO z
                ORDER BY z.Kodikos
                LIMIT ? OFFSET ?
            ");
            break;

        case 'Είδη':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM EIDOS")->fetch_assoc()['count'];
            $stmt = $db->prepare("SELECT * FROM EIDOS LIMIT ? OFFSET ?");
            break;

         case 'Εκδηλώσεις':
                $totalRows = $db->query("SELECT COUNT(*) as count FROM EKDILOSI")->fetch_assoc()['count'];
                $stmt = $db->prepare("
                    SELECT Titlos, Hmerominia, 
                    TIME_FORMAT(Ora, '%H:%i') as Ora,
                    Xwros
                    FROM EKDILOSI 
                    ORDER BY Hmerominia DESC, Ora
                    LIMIT ? OFFSET ?
                ");
             break;

        case 'Εισιτήρια':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM EISITIRIO")->fetch_assoc()['count'];
            $stmt = $db->prepare("
                SELECT * FROM EISITIRIO 
                ORDER BY Hmerominia_Ekdoshs DESC, Kodikos
                LIMIT ? OFFSET ?
            ");
            break;

        case 'Ταμίες':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM TAMIAS")->fetch_assoc()['count'];
            $stmt = $db->prepare("SELECT * FROM TAMIAS LIMIT ? OFFSET ?");
            break;

        case 'Επισκέπτες':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM EPISKEPTIS")->fetch_assoc()['count'];
            $stmt = $db->prepare("SELECT * FROM EPISKEPTIS LIMIT ? OFFSET ?");
            break;

        case 'Φροντιστές':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM FRONTISTIS")->fetch_assoc()['count'];
            $stmt = $db->prepare("
                SELECT f.ID, f.Onoma, f.Eponymo
                FROM FRONTISTIS f
                ORDER BY f.ID
                LIMIT ? OFFSET ?
            ");
            break;

        case 'Προμηθευτές':
            $totalRows = $db->query("SELECT COUNT(*) as count FROM PROMITHEUTIS")->fetch_assoc()['count'];
            $stmt = $db->prepare("
                SELECT p.AFM, p.Onoma, p.Thlefono
                FROM PROMITHEUTIS p
                LIMIT ? OFFSET ?
            ");
            break;

        default:
            throw new Exception("Άγνωστη ενότητα");
    }

    if (isset($stmt)) {
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            array_walk_recursive($row, function(&$item) {
                if (is_string($item)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            });
            $response['data'][] = $row;
        }

        $response['pagination'] = [
            'currentPage' => $page,
            'totalPages' => ceil($totalRows / $limit),
            'totalItems' => $totalRows,
            'itemsPerPage' => $limit
        ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>