<?php
include '../includes/db.php';

function createReport($type, $start_date, $end_date, $generated_by = null) {
    global $conn;

    $sql = "INSERT INTO reports (type, start_date, end_date, generated_by, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $type, $start_date, $end_date, $generated_by);

    if($stmt->execute()) {
        return $conn->insert_id; 
    } else {
        return false;
    }
}

function getReports($limit = 10) {
    global $conn;
    
    // Add error handling
    if (!$conn) {
        error_log("Database connection failed");
        return false;
    }
    
    $sql = "SELECT r.id, r.type, r.start_date, r.end_date, r.created_at, u.username AS generated_by
            FROM reports r
            LEFT JOIN users u ON r.generated_by = u.id
            ORDER BY r.created_at DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $limit);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $reports = [];
    while($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    $stmt->close();
    return $reports;
}

function getReportById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

function generateReport($type, $range, $generated_by) {
    global $conn;

    $dates = getDateRange($range);
    $start = $dates['start'];
    $end = $dates['end'];
    $stmt = null; 

    switch($type){
        case 'sales':
            $sql = "SELECT 
                    s.id, 
                    s.total_price, 
                    s.payment_method, 
                    s.created_at,
                    u.username AS cashier,
                    GROUP_CONCAT(CONCAT(p.name, ' (Qty: ', si.quantity, ', Price: Tshs ', si.price, ')') SEPARATOR '; ') AS items_list
                FROM sales s
                JOIN users u ON s.user_id = u.id
                JOIN sale_items si ON s.id = si.sale_id
                JOIN products p ON si.product_id = p.id
                WHERE s.created_at BETWEEN ? AND ?
                GROUP BY s.id
                ORDER BY s.created_at ASC";
            $stmt = $conn->prepare($sql);
            break;

        case 'expenses':
            $stmt = $conn->prepare("SELECT e.id, e.name, e.amount, ec.name AS category, u.username AS user, e.created_at
                                    FROM expenses e
                                    JOIN users u ON e.user_id = u.id
                                    JOIN expense_categories ec ON e.category_id = ec.id
                                    WHERE e.created_at BETWEEN ? AND ?");
            break;

        case 'banking':
            $stmt = $conn->prepare("SELECT b.id, b.type, b.amount, u.username AS user, b.created_at
                                    FROM banking b
                                    JOIN users u ON b.user_id = u.id
                                    WHERE b.created_at BETWEEN ? AND ?");
            break;

        case 'income':
            $stmt = $conn->prepare("SELECT * FROM income WHERE created_at BETWEEN ? AND ?");
            break;

        case 'inventory':
            $sql = "SELECT p.id, p.name, p.barcode, p.price, p.stock, pc.name AS category, p.created_at 
                    FROM products p 
                    LEFT JOIN product_categories pc ON p.category_id = pc.id";
            $stmt = $conn->prepare($sql);
            break;

        default:
            return ['success'=>false, 'message'=>'Invalid report type'];
    }

    if($type !== 'inventory'){
        $stmt->bind_param("ss", $start, $end);
    }

    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $reportContent = json_encode($data);
    
    $stmtReport = $conn->prepare("INSERT INTO reports (type, start_date, end_date, generated_by, content, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmtReport->bind_param("sssis", $type, $start, $end, $generated_by, $reportContent);
    
    if($stmtReport->execute()) {
        $report_id = $conn->insert_id;
        $stmtReport->close();
        
        return ['success' => true, 'report_id' => $report_id, 'data' => $data, 'type' => $type, 'start_date' => $start, 'end_date' => $end];
    } else {
        $stmtReport->close();
        return ['success' => false, 'message' => 'Failed to save report to database'];
    }
}

function getDateRange($range) {
    $today = date('Y-m-d');
    switch($range){
        case 'today':
            $start = $today;
            $end = $today;
            break;
        case 'week':
            $start = date('Y-m-d', strtotime('-7 days'));
            $end = $today;
            break;
        case 'month':
            $start = date('Y-m-01');
            $end = date('Y-m-t');
            break;
        case 'quarter':
            $quarter = ceil(date('n') / 3); 
            $start = date('Y-m-d', strtotime(date('Y') . '-' . (3*($quarter-1)+1) . '-01'));
            $end = date('Y-m-d', strtotime($start.' +2 months last day of'));
            break;
        case 'year':
            $start = date('Y-01-01');
            $end = date('Y-12-31');
            break;
    }
    return ['start' => $start, 'end' => $end];
}

function downloadReportCSV($reportId) {
    global $conn;

    $stmt = $conn->prepare("SELECT r.*, u.username AS generated_by_name 
                           FROM reports r 
                           LEFT JOIN users u ON r.generated_by = u.id 
                           WHERE r.id = ?");
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$report || empty($report['content'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Report not found or has no content']);
        exit;
    }

    $data = json_decode($report['content'], true);
    
    // Friendly headers mapping
    $friendlyHeaders = [
        'id' => 'ID',
        'cashier' => 'Cashier',
        'total_price' => 'Total Price',
        'items_list' => 'Items Sold',
        'name' => 'Name',
        'product' => 'Product',
        'user' => 'User',
        'username' => 'Username',
        'quantity' => 'Quantity',
        'total_price' => 'Total Price',
        'amount' => 'Amount',
        'price' => 'Price',
        'payment_method' => 'Payment Method',
        'category' => 'Category',
        'type' => 'Type',
        'stock' => 'Stock',
        'barcode' => 'Barcode',
        'category_id' => 'Category ID',
        'created_at' => 'Date'
    ];

    $filename = $report['type'] . '_report_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');

    $output = fopen('php://output', 'w');

    fwrite($output, "\xEF\xBB\xBF");

    if ($data && is_array($data) && !empty($data)) {
        // Use friendly headers
        $headers = array_keys($data[0]);
        $friendlyHeadersRow = [];
        foreach ($headers as $header) {
            $friendlyHeadersRow[] = $friendlyHeaders[$header] ?? ucfirst(str_replace('_', ' ', $header));
        }
        fputcsv($output, $friendlyHeadersRow);

        // Add data rows
        foreach ($data as $row) {
            $formattedRow = [];
            foreach ($row as $key => $value) {
                if ($key === 'total_price' || $key === 'amount' || $key === 'price') {
                    $formattedRow[] = number_format(floatval($value), 2);
                } else {
                    $formattedRow[] = $value;
                }
            }
            fputcsv($output, $formattedRow);
        }
    } else {
        fputcsv($output, ['No data available for this report.']);
    }

    fclose($output);
    exit;
}

function deleteReport($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()) {
        return ['success' => true, 'message' => 'Report deleted successfully.'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete report.'];
    }
}


function printReportHTML($reportId) {
    global $conn;

    // Get report details
    $stmt = $conn->prepare("SELECT r.*, u.username AS generated_by_name 
                           FROM reports r 
                           LEFT JOIN users u ON r.generated_by = u.id 
                           WHERE r.id = ?");
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();
    
    if (!$report) {
        header('Content-Type: text/html');
        echo "<h2>Report not found</h2>";
        exit;
    }

    $data = json_decode($report['content'], true);
    
    // Get friendly column names
    $friendlyHeaders = [
        'id' => 'ID',
        'name' => 'Name',
        'product' => 'Product',
        'user' => 'User',
        'username' => 'Username',
        'quantity' => 'Quantity',
        'total_price' => 'Total Price',
        'amount' => 'Amount',
        'price' => 'Price',
        'payment_method' => 'Payment Method',
        'category' => 'Category',
        'type' => 'Type',
        'stock' => 'Stock',
        'barcode' => 'Barcode',
        'category_id' => 'Category ID',
        'created_at' => 'Date',
        'generated_by' => 'Generated By'
    ];

    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?= htmlspecialchars(ucfirst($report['type'])) ?> Report</title>
        <style>
            @media print {
                body { 
                    font-family: 'Arial', sans-serif; 
                    font-size: 12px; 
                    margin: 0;
                    padding: 15px;
                    color: #000;
                    background: #fff;
                }
                .no-print { display: none !important; }
                .page-break { page-break-after: always; }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin: 10px 0;
                    font-size: 11px;
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 6px; 
                    text-align: left;
                    page-break-inside: avoid;
                }
                th { 
                    background-color: #f5f5f5; 
                    font-weight: bold;
                }
                .report-header { 
                    text-align: center; 
                    margin-bottom: 20px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .report-footer { 
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
                .summary { 
                    margin: 15px 0;
                    padding: 10px;
                    background-color: #f9f9f9;
                    border-left: 4px solid #007bff;
                }
            }
            @media screen {
                body { 
                    font-family: 'Arial', sans-serif; 
                    font-size: 14px; 
                    padding: 20px;
                    background-color: #f8f9fa;
                }
                .print-container { 
                    background: white; 
                    padding: 30px; 
                    border: 1px solid #ddd;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    max-width: 1000px;
                    margin: 0 auto;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin: 15px 0;
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 8px; 
                    text-align: left;
                }
                th { 
                    background-color: #007bff; 
                    color: white;
                }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .report-header { 
                    text-align: center; 
                    margin-bottom: 30px;
                    color: #333;
                }
                .summary { 
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #e9ecef;
                    border-radius: 5px;
                }
                .btn-container { 
                    margin: 20px 0; 
                    text-align: center;
                }
                button { 
                    padding: 10px 20px; 
                    margin: 0 10px; 
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                button:hover { background: #0056b3; }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <div class="report-header">
                <h1><?= htmlspecialchars(ucfirst($report['type'])) ?> Report</h1>
                <p><strong>Business Name:</strong> Your Company Name</p>
                <p><strong>Report Period:</strong> <?= date('M j, Y', strtotime($report['start_date'])) ?> to <?= date('M j, Y', strtotime($report['end_date'])) ?></p>
                <p><strong>Generated On:</strong> <?= date('M j, Y H:i', strtotime($report['created_at'])) ?></p>
                <p><strong>Generated By:</strong> <?= htmlspecialchars($report['generated_by_name'] ?? 'System') ?></p>
            </div>

            <?php if($data && is_array($data) && count($data) > 0): ?>
                <div class="summary">
                    <h3>Report Summary</h3>
                    <p><strong>Total Records:</strong> <?= number_format(count($data)) ?></p>
                    
                    <?php if($report['type'] === 'sales'): ?>
                        <?php $totalSales = array_sum(array_column($data, 'total_price')); ?>
                        <p><strong>Total Sales:</strong> Tshs <?= number_format($totalSales, 2) ?></p>
                    <?php elseif($report['type'] === 'expenses'): ?>
                        <?php $totalExpenses = array_sum(array_column($data, 'amount')); ?>
                        <p><strong>Total Expenses:</strong> Tshs <?= number_format($totalExpenses, 2) ?></p>
                    <?php endif; ?>
                </div>

                <table>
                    <thead>
                        <tr>
                            <?php 
                            $headers = array_keys($data[0]);
                            foreach($headers as $header): 
                                $friendlyHeader = $friendlyHeaders[$header] ?? ucfirst(str_replace('_', ' ', $header));
                            ?>
                                <th><?= htmlspecialchars($friendlyHeader) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $row): ?>
                            <tr>
                                <?php foreach($row as $key => $value): ?>
                                    <td>
                                        <?php if($key === 'total_price' || $key === 'amount' || $key === 'price'): ?>
                                            Tshs <?= number_format(floatval($value), 2) ?>
                                        <?php elseif($key === 'created_at'): ?>
                                            <?= date('M j, Y H:i', strtotime($value)) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($value) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert">
                    <p>No data available for this report.</p>
                </div>
            <?php endif; ?>

            <div class="report-footer">
                <p>Generated on <?= date('F j, Y \a\t H:i') ?> | Page 1 of 1</p>
            </div>

            <div class="btn-container no-print">
                <button onclick="window.print()">Print Report</button>
                <button onclick="window.close()">Close Window</button>
            </div>
        </div>

        <script>
            // Auto-print when window loads
            window.onload = function() {
                // Optional: uncomment to auto-print
                // setTimeout(function() { window.print(); }, 1000);
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}


?>
