<?php
include '../includes/db.php';

function getTotalExpenses($user_id = null, $startDate = null, $endDate = null) {
    global $conn;

    $sql = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE 1=1";
    $params = [];
    $types = "";

    if ($user_id) {
        $sql .= " AND user_id = ? ";
        $params[] = &$user_id;
        $types .= "i";
    }

    if ($startDate && $endDate) {
        $sql .= " AND created_at BETWEEN ? AND ? ";
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

    return $result['total_expenses'] ?? 0;
}


function createExpense($name, $amount, $category, $user_id) {
    global $conn;

   $stmt = $conn->prepare("INSERT INTO expenses (name, amount, category_id, user_id, created_at) 
                      VALUES (?,?,?,?,NOW())");
    $stmt->bind_param("sdsi", $name, $amount, $category, $user_id);

    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }
    return false;
}

function getExpenses($user_id = null, $search = null, $startDate = null, $endDate = null, $limit = 10, $offset = 0) {
    global $conn;

    $sql = "SELECT e.id, e.name, e.amount, c.name AS category_name, u.username AS user, e.created_at
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            JOIN users u ON e.user_id = u.id
            WHERE 1 ";

    $params = [];
    $types = "";

    if ($user_id) {
        $sql .= " AND e.user_id = ? ";
        $params[] = &$user_id;
        $types .= "i";
    }

    if (!empty($search)) {
        $sql .= " AND (e.name LIKE ? OR c.name LIKE ? OR u.username LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $params[] = &$like;
        $types .= "sss";
    }

    if ($startDate && $endDate) {
        $sql .= " AND e.created_at BETWEEN ? AND ? ";
        $params[] = &$startDate;
        $params[] = &$endDate;
        $types .= "ss";
    }

    $sql .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
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

    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }

    return $expenses;
}

function getTotalExpensesCount($user_id = null, $search = null, $startDate = null, $endDate = null) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total_count FROM expenses e
            JOIN categories c ON e.category_id = c.id
            JOIN users u ON e.user_id = u.id
            WHERE 1";
    $params = [];
    $types = "";

    if ($user_id) {
        $sql .= " AND e.user_id = ? ";
        $params[] = &$user_id;
        $types .= "i";
    }
    if (!empty($search)) {
        $sql .= " AND (e.name LIKE ? OR c.name LIKE ? OR u.username LIKE ?)";
        $like = "%$search%";
        $params[] = &$like;
        $params[] = &$like;
        $params[] = &$like;
        $types .= "sss";
    }
    if ($startDate && $endDate) {
        $sql .= " AND e.created_at BETWEEN ? AND ? ";
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


function getExpenseById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT e.id, e.name, e.amount, e.category_id, u.username AS user, e.created_at
                          FROM expenses e
                          JOIN users u ON e.user_id = u.id
                          WHERE e.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function deleteExpense($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function updateExpense($id, $name, $amount, $category) {
    global $conn;

    $stmt = $conn->prepare("UPDATE expenses 
                          SET name = ?, amount = ?, category_id = ?
                          WHERE id = ?");
    $stmt->bind_param("sdsi", $name, $amount, $category, $id);

    return $stmt->execute();
}

