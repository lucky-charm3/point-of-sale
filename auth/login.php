<?php
 include 'login_process.php';
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>POS System Login</h2>
        <?php if(isset($error)) { echo "<p class='error-msg'>$error</p>"; } ?>
        <form method="POST" action="">
    <div class="form-group">
        <input type="text" name="username" placeholder="Username">
    </div>
    <div class="form-group">
        <input type="password" name="password" placeholder="Password">
    </div>
    <button type="submit" name="login">Login</button>
</form>
    </div>
    <script src="validate-login.js"></script>
</body>
</html>

