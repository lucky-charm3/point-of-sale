<?php
require_once '../includes/db.php';

function getAllProducts($search = null, $limit = 50, $offset = 0) {
    global $conn;
    $sql = "SELECT id, name, barcode, price, stock, category_id, created_at FROM products WHERE 1=1";
    $params = [];
    $types = "";

    if(!empty($search)) {
        $sql .= " AND (name LIKE ? OR barcode LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $types .= "ss";
    }

    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = &$limit;
    $params[] = &$offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalProducts($search = null) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total_products FROM products WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR barcode LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $types .= "ss";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total_products'] ?? 0;
}


function getProductById($id){
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, barcode, price, stock, category_id, created_at FROM products WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createProduct($name, $barcode, $price, $stock, $category_id){
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM products WHERE barcode=?");
    $stmt->bind_param("s",$barcode);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0){
        return ['success'=>false,'message'=>'Barcode already exists'];
    }

    $stmt = $conn->prepare("INSERT INTO products (name, barcode, price, stock, category_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdii",$name, $barcode, $price, $stock, $category_id);
    if($stmt->execute()){
        return ['success'=>true,'id'=>$conn->insert_id];
    } else {
        return ['success'=>false,'message'=>$conn->error];
    }
}

function updateProduct($id, $name, $barcode, $price, $stock, $category_id){
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET name=?, barcode=?, price=?, stock=?, category_id=? WHERE id=?");
    $stmt->bind_param("ssdiii",$name, $barcode, $price, $stock, $category_id, $id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false,'message'=>$conn->error];
}

function deleteProduct($id){
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i",$id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false,'message'=>$conn->error];
}
