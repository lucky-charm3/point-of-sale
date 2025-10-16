<?php
require_once '../controllers/categories-controller.php';

header('Content-Type: application/json');
session_start();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager')) {
            $search = $_GET['search'] ?? null;
            $categories = getCategories($search);
            echo json_encode($categories);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
        break;

    case 'getById':
        $id = $_GET['id'] ?? 0;
        $category = getCategoryById($id);
        echo json_encode($category);
        break;

    case 'add':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                exit;
            }
            
            $name = $_POST['name'] ?? null;
            if (!$name) {
                echo json_encode(['success' => false, 'message' => 'Missing required data.']);
                exit;
            }
            
            $result = createCategory($name);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
        break;
    
    case 'update':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($data['id']) || !isset($data['name'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required data.']);
                exit;
            }

            $result = updateCategory($data['id'], $data['name']);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
        }
        break;

    case 'delete':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? 0;
                $result = deleteCategory($id);
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
