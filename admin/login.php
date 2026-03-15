<?php
require_once __DIR__ . '/config.php';

session_name(SESSION_NAME);
session_start();

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – TableSports</title>
<style>
body {
    font-family: system-ui, sans-serif;
    background: #0f172a;
    color: #e5e7eb;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}
.login-box {
    background: #1e293b;
    border-radius: 10px;
    padding: 40px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}
h1 {
    text-align: center;
    color: #38bdf8;
    margin: 0 0 30px;
    font-size: 1.6rem;
}
label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.9rem;
    color: #94a3b8;
}
input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #334155;
    border-radius: 6px;
    background: #0f172a;
    color: #e5e7eb;
    font-size: 1rem;
    box-sizing: border-box;
    margin-bottom: 20px;
}
input[type="password"]:focus {
    outline: none;
    border-color: #38bdf8;
}
button[type="submit"] {
    width: 100%;
    padding: 11px;
    background: #38bdf8;
    color: #0f172a;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
}
button[type="submit"]:hover {
    background: #0ea5e9;
}
.error {
    color: #f87171;
    font-size: 0.88rem;
    margin-bottom: 14px;
    text-align: center;
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #94a3b8;
    font-size: 0.85rem;
    text-decoration: none;
}
.back-link:hover { color: #38bdf8; }
</style>
</head>
<body>
<div class="login-box">
    <h1>🔐 Admin</h1>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" autofocus required>
        <button type="submit">Ingresar</button>
    </form>
    <a class="back-link" href="../index.php">← Volver al inicio</a>
</div>
</body>
</html>
