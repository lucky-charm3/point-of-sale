<?php
require_once '../controllers/sales-controller.php';
header('Content-Type: application/json');

session_start();

$action = $_GET['action'] ?? '';
$userId=$_SESSION['user_id'];
$userRole=$_SESSION['role'];

switch ($action) {

    case 'getAll':
    if (isset($userRole) && ($userRole === 'admin' || $userRole === 'manager')) {
        $search = $_GET['search'] ?? null;
        $limit = $_GET['limit'] ?? null;
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * ($limit ?? 10);
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $sales = getSales($search, $startDate, $endDate, $limit, $offset);
        $total = getTotalSalesCount($search, $startDate, $endDate);
        
        echo json_encode(['sales' => $sales, 'total' => $total]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    }
    break;

case 'getCashierSales':
    if ($userRole === 'cashier' && $userId !== null) {
        $search = $_GET['search'] ?? null;
        $limit = intval($_GET['limit'] ?? 10);
        $page = intval($_GET['page'] ?? 1);
        $offset = ($page - 1) * $limit;
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d 23:59:59');

        $sales = getSales($search, $startDate, $endDate, $limit, $offset, $userId);
        $totalSales = getTotalSalesCount($search, $startDate, $endDate, $userId);

        echo json_encode([
            'success' => true,
            'sales' => $sales,
            'total' => $totalSales,
            'message' => count($sales) > 0 ? 'Sales retrieved successfully' : 'No sales found'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'sales' => [],
            'total' => 0,
            'message' => 'Unauthorized action'
        ]);
    }
    break;

    case 'getTotalSalesToday':
        if ($userRole === 'cashier' && $userId !== null) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d 23:59:59');
            $totalSales = getTotalSales($startDate, $endDate, $userId);
            echo json_encode(['success' => true, 'total_sales' => number_format($totalSales, 2)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
        break;

    case 'getById':
        $id = $_GET['id'] ?? 0;
        $sale = getSaleById($id);
        echo json_encode($sale);
        break;

    case 'add':
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'cashier')) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    $rawData = json_decode(file_get_contents("php://input"), true);

    if (!is_array($rawData)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON payload received by the server.']);
        exit;
    }

    $items = $rawData['items'] ?? null;
    $payment_method = $rawData['payment_method'] ?? null;
    $total_price = $rawData['total_price'] ?? null; 

    if (!$items || !$payment_method || !$total_price) {
        echo json_encode(['success' => false, 'message' => 'Missing required data.']);
        exit;
    }

    $userId = (int)$_SESSION['user_id'];
    
    $result = createSale($userId, $items, $payment_method);
    
    echo json_encode($result);

} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
}
break;
    
    case 'delete':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? 0;
                $result = deleteSale($id);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request method. Please use POST for deletion.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}