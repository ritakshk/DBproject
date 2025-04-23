<?php
// cashier.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] !== 'cashier') {
    header('Location: login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer    = $conn->real_escape_string($_POST['customer']);
    $drink_id    = intval($_POST['drink_id']);
    $topping_id  = !empty($_POST['topping_id']) ? intval($_POST['topping_id']) : null;
    $temperature = $conn->real_escape_string($_POST['temperature']);
    $placed_by   = $_SESSION['eid'];

    $stmt = $conn->prepare("
      INSERT INTO orders
        (customer_name, drink_id, topping_id, temperature, placed_by)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
      'siisi',
      $customer,
      $drink_id,
      $topping_id,
      $temperature,
      $placed_by
    );
    if ($stmt->execute()) {
        $message = 'Order placed successfully.';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}

$drinks   = $conn->query("SELECT drink_id, name FROM drinks ORDER BY name");
$toppings = $conn->query("SELECT topping_id, name FROM toppings ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cashier – Place Order</title>
  <style>
    html, body { height: 100%; margin: 0; }
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      background: #dfd8cf;
      font-family: Georgia, serif;
    }
    .container {
      background: #ece7e0;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 360px;
    }
    h1 {
      text-align: center;
      margin: 0 0 20px;
    }
    label, select, input, button {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 8px;
      font-size: 1em;
    }
    .message {
      text-align: center;
      color: green;
      margin-bottom: 10px;
    }
    p.logout {
      text-align: center;
      margin-top: 20px;
    }
    p.logout a {
      color: #946f63;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Cashier Dashboard</h1>

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Customer Name:
        <input type="text" name="customer" required>
      </label>

      <label>Drink:
        <select name="drink_id" required>
          <?php while ($d = $drinks->fetch_assoc()): ?>
            <option value="<?= $d['drink_id'] ?>">
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </label>

      <label>Topping (optional):
        <select name="topping_id">
          <option value="">— None —</option>
          <?php while ($t = $toppings->fetch_assoc()): ?>
            <option value="<?= $t['topping_id'] ?>">
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </label>

      <label>Temperature:
        <select name="temperature" required>
          <option value="hot">Hot</option>
          <option value="cold">Cold</option>
        </select>
      </label>

      <button type="submit">Place Order</button>
    </form>

    <p class="logout"><a href="logout.php">Log Out</a></p>
  </div>
</body>
</html>

