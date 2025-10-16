<?php
session_start();
require_once '../controllers/users-controller.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$userId = $_SESSION['user_id'] ?? 0;

switch($action){
    case 'getAll':
        if(isset($_SESSION['role']) && ($_SESSION['role']==='admin'||$_SESSION['role']==='manager'))
        {
            $search = $_GET['search'] ?? null;
            $limit = $_GET['limit'] ?? 20;
            $offset = $_GET['offset'] ?? 0;
            $users = getAllUsers($search, $limit, $offset);
            echo json_encode(['success' => true, 'data' => $users]);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'getCashiers':
        if(isset($_SESSION['role']) && ($_SESSION['role']==='admin'||$_SESSION['role']==='manager'))
        {
            $search = $_GET['search'] ?? null;
            $limit = $_GET['limit'] ?? 20;
            $offset = $_GET['offset'] ?? 0;
            $cashiers = getAllUsersByRole('cashier', $search, $limit, $offset);
            echo json_encode(['success' => true, 'data' => $cashiers]);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'getById':
        if(isset($_SESSION['role']) && ($_SESSION['role']==='admin'||$_SESSION['role']==='manager'))
        {
            $id = $_GET['id'] ?? 0;
            $user = getUserById($id);
            echo json_encode(['success' => true, 'data' => $user]);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'getMe':
        $user = getUserById($userId);
        echo json_encode(['success' => true, 'data' => $user]);
        break;

    case 'add':
        if(isset($_SESSION['role']) && ($_SESSION['role']==='admin'||$_SESSION['role']==='manager'))
        {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = createUser($data['username'], $data['email'], $data['phone'], $data['password'], $data['role']);
            echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'update':
        if(isset($_SESSION['role']) && ($_SESSION['role']==='admin'||$_SESSION['role']==='manager'))
        {
            $data = json_decode(file_get_contents("php://input"), true);
            $password = $data['password'] ?? null;
            $result = updateUser($data['id'], $data['email'], $data['phone'], $data['role'], $password);
            echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'delete':
        if(isset($_SESSION['role']) && $_SESSION['role']==='admin')
        {
            $id = $_GET['id'] ?? 0;
            $result = deleteUser($id);
            echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'updateProfile':
        $data = json_decode(file_get_contents("php://input"), true);
        $result = updateUserProfile($userId, $data['email'], $data['phone']);
        echo json_encode($result);
        break;

    case 'changePassword':
        $data = json_decode(file_get_contents("php://input"), true);
        $result = updateUserPassword($userId, $data['current_password'], $data['new_password']);
        echo json_encode($result);
        break;

    case 'lockAccount':
        $result = lockUser($userId);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success'=>false, 'message'=>'Invalid action']);
}