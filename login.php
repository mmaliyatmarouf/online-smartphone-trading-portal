<?php
// login.php — place in: C:\xampp\htdocs\portal\login.php
session_start();
require_once("includes/db.php");

// Already logged in? Go to my_phones
if (isset($_SESSION['user_id'])) {
    header("Location: /portal/my_phones.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Support both plain-text and hashed passwords
        $passwordMatch = ($password === $user['password'])
            || (isset($user['password']) && password_verify($password, $user['password']));

        if ($passwordMatch) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: /portal/admin_dashboard.php");
            } else {
                header("Location: /portal/my_phones.php");
            }
            exit();
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📱 TradePortal – Login</title>
  <link rel="stylesheet" href="/portal/includes/style.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; }
    .login-wrap { width:100%; max-width:400px; }
    .login-wrap h1 { font-size:2rem; font-weight:800; margin-bottom:0.3rem; }
    .login-wrap .sub { color:var(--text-muted); font-size:0.9rem; margin-bottom:1.8rem; }
  </style>
</head>
<body>
<div class="login-wrap">
  <h1>📱 TradePortal</h1>
  <p class="sub">Online Smartphone Trading Portal</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="glass-card">
    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@email.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:0.5rem;">Log In</button>
    </form>
  </div>
</div>
</body>
</html>
