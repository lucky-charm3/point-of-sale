<?php
require_once '../includes/db.php';

function getCategories($search = null) {
    global $conn;

    $sql = "SELECT id, name FROM categories";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " WHERE name LIKE ?";
        $like = "%$search%";
        $params[] = $like;
        $types .= "s";
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        error_log("Get result failed: (" . $stmt->errno . ") " . $stmt->error);
        return [];
    }
}

function getCategoryById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createCategory($name) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        return ['success' => true, 'id' => $conn->insert_id];
    } else {
        error_log("Database error: " . $conn->error);
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
}

function updateCategory($id, $name) {
    global $conn;
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);

    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => $conn->error];
    }
}

function deleteCategory($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => $conn->error];
    }
}
