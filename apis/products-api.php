<?php
session_start();
require_once '../controllers/products-controller.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch($action){
    case 'getAll':
    $search = $_GET['search'] ?? null;
    $products = getAllProducts($search);
    echo json_encode(['data' => $products]);
    break;

    case 'getById':
        $id = $_GET['id'] ?? 0;
        $product = getProductById($id);
        echo json_encode($product);
        break;

    case 'add':
        if ($_SESSION['role']=='admin'||$_SESSION['role']=='manager')
        {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = createProduct($data['name'], $data['barcode'], $data['price'], $data['stock'], $data['category_id']);
        echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Not authorized']);
        }
        break;

    case 'update':
        if($_SESSION['role']=='admin'||$_SESSION['role']=='manager')
        {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = updateProduct($data['id'], $data['name'], $data['barcode'], $data['price'], $data['stock'], $data['category_id']);
        echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Not authorized']);
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_SESSION['role'] === 'admin') {
                parse_str(file_get_contents("php://input"), $post_vars);
                $id = intval($post_vars['id'] ?? 0);
                $result = deleteProduct($id);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not authorized']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method. Please use POST.']);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Invalid action']);
}
