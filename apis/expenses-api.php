<?php
session_start();
require_once "../controllers/expenses-controller.php";

$action = $_GET['action'] ?? '';
$search = $_GET['search'] ?? '';

switch ($action) {
    case 'add':
    if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager')) {
        $name = $_POST['name'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $category = $_POST['category'] ?? null;
        $user_id = $_SESSION['user_id'];

        if (!$name || !$amount || !$category || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'Missing required data.']);
            exit;
        }

        $result = createExpense($name, $amount, $category, $user_id); 
        echo json_encode(['success' => !!$result, 'id' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    }
    break;

   case 'getAll':
    $search = $_GET['search'] ?? null;
    $limit = intval($_GET['limit'] ?? 10);
    $page = intval($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;

    $expenses = getExpenses(null, $search, null, null, $limit, $offset);
    $total = getTotalExpensesCount(null, $search);

    echo json_encode(['data' => $expenses, 'total' => $total]);
    break;

    case 'getById':
        $id = intval($_GET['id'] ?? 0);
        $expense = getExpenseById($id);
        echo json_encode($expense);
        break;

    case 'update':
         if($_SESSION['role']==='admin'||$_SESSION['role']==='manager')
         {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = updateExpense(
            $data['id'],
            $data['name'],
            $data['amount'],
            $data['category'],
        );
        echo json_encode(['success' => $result]);
    }
    else
    {
        echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
    }
        break;

    case 'delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['role'] === 'admin') {
            $id = intval($_POST['id'] ?? 0);
            $result = deleteExpense($id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method. Please use POST.']);
    }
    break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
