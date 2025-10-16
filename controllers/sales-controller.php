<?php
require_once '../includes/db.php';

function getTotalSales($startDate = null, $endDate = null, $userId = null) {
    global $conn;

    $sql = "SELECT SUM(total_price) AS total_sales FROM sales WHERE 1=1";
    $params = [];
    $types = "";

    if ($startDate && $endDate) {
        $sql .= " AND created_at BETWEEN ? AND ?";
        $params[] = &$startDate;
        $params[] = &$endDate;
        $types .= "ss";
    }
    
    // Add user filter
    if ($userId !== null) {
        $sql .= " AND user_id = ?";
        $params[] = &$userId;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);

    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total_sales'] ?? 0;
}

function getTotalSalesCount($search = '', $startDate = null, $endDate = null, $userId = null) {
    global $conn;

    $sql = "SELECT COUNT(DISTINCT s.id) AS total_sales FROM sales s JOIN sale_items si ON s.id = si.sale_id JOIN products p ON si.product_id = p.id WHERE 1";
    $params = [];
    $types = "";

    if ($userId !== null) {
        $sql .= " AND s.user_id = ?";
        $params[] = &$userId;
        $types .= "i";
    }
    if (!empty($search)) {
        $sql .= " AND p.name LIKE ?";
        $like = "%$search%";
        $params[] = &$like;
        $types .= "s";
    }
    if ($startDate && $endDate) {
        $sql .= " AND s.created_at BETWEEN ? AND ?";
        $params[] = &$startDate;
        $params[] = &$endDate;
        $types .= "ss";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['total_sales'] ?? 0;
}

function getSales($search = '', $startDate = null, $endDate = null, $limit = null, $offset = 0, $userId = null) {
    global $conn;
    
    $sql = "SELECT s.id, u.username AS user, s.total_price, s.payment_method, s.created_at,
            GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ')') SEPARATOR ', ') AS products
            FROM sales s
            JOIN users u ON s.user_id = u.id
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (u.username LIKE ? OR p.name LIKE ? OR s.payment_method LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $params[] = &$like;
        $types .= "sss";
    }

    if ($startDate && $endDate) {
        $sql .= " AND s.created_at BETWEEN ? AND ?";
        $params[] = &$startDate;
        $params[] = &$endDate;
        $types .= "ss";
    }
    
    if ($userId !== null) {
        $sql .= " AND s.user_id = ?";
        $params[] = &$userId;
        $types .= "i";
    }

    $sql .= " GROUP BY s.id ORDER BY s.created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = &$limit;
        $params[] = &$offset;
        $types .= "ii";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $sales = [];
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
    }
    
    return $sales;
}

function getSaleById($id) {
    global $conn;
    
    // Get sale basic info
    $stmt = $conn->prepare("SELECT s.id, u.username AS user, s.total_price, s.payment_method, s.created_at
                            FROM sales s
                            JOIN users u ON s.user_id = u.id
                            WHERE s.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();
    
    if (!$sale) {
        return null;
    }
    
    // Get sale items
    $stmt = $conn->prepare("SELECT p.name AS product, si.quantity, si.price
                            FROM sale_items si
                            JOIN products p ON si.product_id = p.id
                            WHERE si.sale_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $sale['items'] = $items;
    
    $productNames = array_map(function($item) {
        return $item['product'] . ' (' . $item['quantity'] . ')';
    }, $items);
    
    $sale['product'] = implode(', ', $productNames);
    $sale['quantity'] = array_sum(array_column($items, 'quantity'));
    
    return $sale;
}

function getSaleDetails($saleId) {
    global $conn;

    $stmt = $conn->prepare("SELECT s.id, s.total_price, s.payment_method, s.created_at, u.username AS cashier
                          FROM sales s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
    $stmt->bind_param("i", $saleId);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$sale) {
        return null;
    }

    $stmt = $conn->prepare("SELECT p.name AS product_name, p.price, si.quantity, (si.quantity * p.price) AS item_total
                          FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
    $stmt->bind_param("i", $saleId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $sale['items'] = $items;
    return $sale;
}

function createSale($userId, $items, $payment_method) {
    global $conn;

    if (empty($items) || !is_array($items)) {
        return ['success' => false, 'message' => 'No items provided for the sale.'];
    }

    $conn->begin_transaction();
    $totalPrice = 0;

    try {
        $stmt = $conn->prepare("INSERT INTO sales (user_id, total_price, payment_method) VALUES (?, ?, ?)");

        $tempPrice = 0;
        $stmt->bind_param("ids", $userId, $tempPrice, $payment_method);
        $stmt->execute();
        $saleId = $conn->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_product = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt_update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            $stmt_product->bind_param("i", $productId);
            $stmt_product->execute();
            $product = $stmt_product->get_result()->fetch_assoc();

            if (!$product || $product['stock'] < $quantity) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Insufficient stock for a product or product not found.'];
            }

            $itemPrice = $product['price'];
            $stmt_item->bind_param("iiid", $saleId, $productId, $quantity, $itemPrice);
            $stmt_item->execute();
            $totalPrice += $itemPrice * $quantity;

            $stmt_update_stock->bind_param("ii", $quantity, $productId);
            $stmt_update_stock->execute();
        }

        $stmt_total = $conn->prepare("UPDATE sales SET total_price = ? WHERE id = ?");
        $stmt_total->bind_param("di", $totalPrice, $saleId);
        $stmt_total->execute();
        
        $conn->commit();
        
        return ['success' => true, 'id' => $saleId, 'total_price' => $totalPrice];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function deleteSale($id){
    global $conn;
    $stmt = $conn->prepare("DELETE FROM sales WHERE id=?");
    $stmt->bind_param("i",$id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false,'message'=>$conn->error];
}
