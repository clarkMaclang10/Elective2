<?php
session_start();

// File to store user data
$usersFile = 'users.txt';

// Determine which form to show
$showRegister = isset($_GET['register']) || isset($_POST['show_register']);

// Handle registration
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = trim($_POST['reg_password']);
    if ($username && $email && $password) {
        $users = file_exists($usersFile) ? file($usersFile, FILE_IGNORE_NEW_LINES) : [];
        foreach ($users as $user) {
            list($u, $e) = explode(':', $user);
            if ($u === $username) {
                $reg_error = 'Username already exists!';
                break;
            }
            if ($e === $email) {
                $reg_error = 'Email already exists!';
                break;
            }
        }
        if (!isset($reg_error)) {
            $entry = $username . ':' . $email . ':' . password_hash($password, PASSWORD_DEFAULT) . ':1000';
            file_put_contents($usersFile, $entry . "\n", FILE_APPEND);
            $reg_success = 'Account created! Please log in.';
            $showRegister = false;
        }
    } else {
        $reg_error = 'Please fill all fields.';
        $showRegister = true;
    }
}

// Handle login
if (isset($_POST['login'])) {
    $user_input = trim($_POST['username']); // can be username or email
    $password = trim($_POST['password']);
    $users = file_exists($usersFile) ? file($usersFile, FILE_IGNORE_NEW_LINES) : [];
    foreach ($users as $user) {
        list($u, $e, $p, $money) = explode(':', $user);
        if (($u === $user_input || $e === $user_input) && password_verify($password, $p)) {
            $_SESSION['username'] = $u;
            $_SESSION['money'] = (int)$money;
            header('Location: index.php');
            exit;
        }
    }
    $login_error = 'Invalid credentials!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <?php if (!$showRegister): ?>
            <h2>Login</h2>
            <?php if (isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
            <?php if (isset($reg_success)) echo "<p class='success'>$reg_success</p>"; ?>
            <form method="post">
                <input type="text" name="username" placeholder="Username or Email" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button type="submit" name="login">Login</button>
            </form>
            <form method="post" style="margin-top:10px;">
                <button type="submit" name="show_register">Register</button>
            </form>
        <?php else: ?>
            <h2>Register</h2>
            <?php if (isset($reg_error)) echo "<p class='error'>$reg_error</p>"; ?>
            <form method="post">
                <input type="text" name="reg_username" placeholder="Username" required><br>
                <input type="email" name="reg_email" placeholder="Email" required><br>
                <input type="password" name="reg_password" placeholder="Password" required><br>
                <button type="submit" name="register">Register</button>
            </form>
            <form method="get" style="margin-top:10px;">
                <button type="submit">Back to Login</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
