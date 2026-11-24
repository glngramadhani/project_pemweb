<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$saved_username = isset($_COOKIE['remember_admin']) ? $_COOKIE['remember_admin'] : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = hash('sha256', $_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT id, username FROM users WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['admin'] = $row['username'];

        if (isset($_POST['remember'])) {
            setcookie('remember_admin', $row['username'], time() + (86400 * 7), "/");
        } else {
            setcookie('remember_admin', '', time() - 3600, "/");
        }

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="card auth-card">
        <h2>Login Perpustakaan</h2>
        <p class="subtitle">Masuk dengan <b>admin</b> / <b>admin123</b></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($saved_username) ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="remember" name="remember" <?= $saved_username ? 'checked' : '' ?>>
                <label for="remember">Ingat Saya (Cookie)</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</body>
</html>