<?php
require_once '../controllers/reports-controller.php';
header('Content-Type: application/json');
session_start();

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

switch($action){

    case 'generate':
    $data = json_decode(file_get_contents("php://input"), true);
    $type = $data['type'] ?? '';
    $dateRange = $data['dateRange'] ?? 'today';
    $report = generateReport($type, $dateRange, $userId);     
    echo json_encode($report);
    break;

    case 'getAll':
        $limit = $_GET['limit'] ?? 10;
        $reports = getReports($limit);
        echo json_encode($reports);
        break;

    case 'getById':
        $id = $_GET['id'] ?? 0;
        $report = getReportById($id);
        echo json_encode($report);
        break;

    case 'delete':
        if($_SESSION['role']==='admin')
        {
        $id = $_GET['id'] ?? 0;
        $result = deleteReport($id);
        echo json_encode($result);
        }
        else
        {
            echo json_encode(['success'=>false,'message'=>'Unauthorized action']);
        }
        break;

    case 'download':
        $id = $_GET['id'] ?? 0;
        downloadReportCSV($id); 
        break;

    case 'print':
        $id = $_GET['id'] ?? 0;
        printReportHTML($id);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Invalid action']);
}
