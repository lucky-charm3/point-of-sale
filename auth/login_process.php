<?php
session_start();
include "../includes/db.php";

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $sql->bind_param('s', $username);
    $sql->execute();
    $result = $sql->get_result();

    if($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] === 'admin') {
                header('Location: ../dashboards/admin-dashboard.php');
                exit();
            } elseif($user['role'] === 'manager') {
                header('Location: ../dashboards/manager-dashboard.php');
                exit();
            } elseif($user['role'] === 'cashier') {
                header('Location: ../dashboards/cashier-dashboard.php');
                exit();
            } 
            else {
                header('Location: ../index.php');
                exit();
            }
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>
