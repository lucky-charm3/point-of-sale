<?php
include '../includes/db.php';

function createBanking($type, $amount, $user_id) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO banking (type, amount, user_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sdi", $type, $amount, $user_id);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function getBanking($user_id = null, $search = null, $startDate = null, $endDate = null, $limit = 10, $offset = 0) {
    global $conn;

    $sql = "SELECT b.id, b.type, b.amount, u.username AS user, b.created_at
            FROM banking b
            JOIN users u ON b.user_id = u.id
            WHERE 1 ";

    $params = [];
    $types = "";

    if ($user_id) {
        $sql .= " AND b.user_id = ? ";
        $params[] = &$user_id;
        $types .= "i";
    }

    if (!empty($search)) {
        $sql .= " AND (b.type LIKE ? OR u.username LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $types .= "ss";
    }

    if ($startDate && $endDate) {
        $sql .= " AND b.created_at BETWEEN ? AND ? ";
        $params[] = &$startDate;
        $params[] = &$endDate;
        $types .= "ss";
    }

    $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    $params[] = &$limit;
    $params[] = &$offset;
    $types .= "ii";

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

    $banking = [];
    while ($row = $result->fetch_assoc()) {
        $banking[] = $row;
    }

    return $banking;
}

function getTotalBankingCount($user_id = null, $search = null, $startDate = null, $endDate = null) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total_count FROM banking b JOIN users u ON b.user_id = u.id WHERE 1";
    $params = [];
    $types = "";

    if ($user_id) {
        $sql .= " AND b.user_id = ? ";
        $params[] = &$user_id;
        $types .= "i";
    }
    if (!empty($search)) {
        $sql .= " AND (b.type LIKE ? OR u.username LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $types .= "ss";
    }
    if ($startDate && $endDate) {
        $sql .= " AND b.created_at BETWEEN ? AND ? ";
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
    return $result['total_count'] ?? 0;
}


function getBankingById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT b.id, b.type, b.amount, u.username AS user, b.created_at
                          FROM banking b
                          JOIN users u ON b.user_id = u.id
                          WHERE b.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateBanking($id, $type, $amount) {
    global $conn;

    $stmt = $conn->prepare("UPDATE banking SET type = ?, amount = ? WHERE id = ?");
    $stmt->bind_param("sdi", $type, $amount, $id);
    return $stmt->execute();
}

function deleteBanking($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM banking WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
