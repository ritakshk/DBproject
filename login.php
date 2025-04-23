<?php
// login.php
session_start();
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid      = intval($_POST['employee_id']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
      SELECT employee_id, role
      FROM employees
      WHERE employee_id = ?
        AND password_hash = MD5(?)
    ");
    $stmt->bind_param('is', $eid, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $role);
        $stmt->fetch();
        $_SESSION['eid']  = $id;
        $_SESSION['role'] = $role;
        header("Location: " . ($role === 'cashier' ? 'cashier.php' : 'barista.php'));
        exit;
    } else {
        $error = 'Invalid employee ID or password.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login – Boba Query</title>
  <style>
    html, body { height: 100%; margin: 0; }
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #dfd8cf;
      font-family: Georgia, serif;
    }
    .logo {
      display: block;
      max-width: 200px;
      margin-bottom: 20px;
    }
    form {
      background: #ece7e0;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 320px;
    }
    label, input, button {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 8px;
      font-size: 1em;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <img src="logo.png" alt="Boba Query Logo" class="logo">
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post">
    <label>Employee ID:
      <input type="number" name="employee_id" required>
    </label>
    <label>Password:
      <input type="password" name="password" required>
    </label>
    <button type="submit">Log In</button>
  </form>
</body>
</html>

