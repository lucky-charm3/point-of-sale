<?php
require_once '../includes/db.php';

function getTotalUsers($search = null) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total_users FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $params[] = &$like;
        $types .= "sss";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total_users'] ?? 0;
}

function getTotalCashiers($search = null) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total_cashiers FROM users WHERE role = 'cashier'";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $params[] = &$like;
        $types .= "sss";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total_cashiers'] ?? 0;
}

function getAllUsers($search = null, $limit = 20, $offset = 0) {
    global $conn;
    $sql = "SELECT id, username, email, phone, role, created_at FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= "sss";
    }

    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllUsersByRole($role, $search = null, $limit = 20, $offset = 0) {
    global $conn;
    $sql = "SELECT id, username, email, phone, role, created_at FROM users WHERE role = ?";
    $params = [$role];
    $types = "s";

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= "sss";
    }

    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserById($id){
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, phone, role, created_at FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createUser($username, $email, $phone, $password, $role) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        return ['success'=>false, 'message'=>'Username or email already exists'];
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $phone, $hashed, $role);

    if($stmt->execute()){
        return ['success'=>true, 'id'=>$conn->insert_id];
    } else {
        return ['success'=>false, 'message'=>$conn->error];
    }
}

function updateUser($id, $email, $phone, $role, $password = null){
    global $conn;
    if($password){
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET email=?, phone=?, role=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $email, $phone, $role, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET email=?, phone=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $email, $phone, $role, $id);
    }
    return $stmt->execute() ? ['success'=>true] : ['success'=>false, 'message'=>$conn->error];
}

function deleteUser($id){
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i",$id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false, 'message'=>$conn->error];
}

function updateUserProfile($id, $email, $phone){
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET email=?, phone=? WHERE id=?");
    $stmt->bind_param("ssi", $email, $phone, $id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false, 'message'=>$conn->error];
}

function updateUserPassword($id, $currentPassword, $newPassword){
    global $conn;

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $hashedPassword = $result['password'] ?? '';

    if (!password_verify($currentPassword, $hashedPassword)) {
        return ['success' => false, 'message' => 'Incorrect current password.'];
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hashed, $id);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Password updated successfully.'];
    } else {
        return ['success' => false, 'message' => 'Failed to update password.'];
    }
}

function lockUser($id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET is_locked=1 WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute() ? ['success'=>true] : ['success'=>false, 'message'=>$conn->error];
}