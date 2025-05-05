<?php
// cashier.php
session_start();
require_once 'config.php';

// 1) Protect: only logged-in cashiers
if (!isset($_SESSION['eid']) || $_SESSION['role'] !== 'cashier') {
    header('Location: login.php');
    exit;
}

$message   = '';
$is_edit   = false;
$order_id  = null;
$customer  = '';
$drink_id  = '';
$topping_id = '';
$temperature = 'hot';

// 2) Handle POST: update vs. place
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_order'])) {
        // --- UPDATE existing order ---
        $order_id   = intval($_POST['order_id']);
        $customer   = $conn->real_escape_string($_POST['customer']);
        $drink_id   = intval($_POST['drink_id']);
        $topping_id = !empty($_POST['topping_id']) ? intval($_POST['topping_id']) : null;
        $temperature= $conn->real_escape_string($_POST['temperature']);

        $stmt = $conn->prepare("
          UPDATE orders
             SET customer_name = ?,
                 drink_id      = ?,
                 topping_id    = ?,
                 temperature   = ?
           WHERE order_id = ?
        ");
        $stmt->bind_param('siisi',
            $customer,
            $drink_id,
            $topping_id,
            $temperature,
            $order_id
        );
        if ($stmt->execute()) {
            $message = "Order #{$order_id} updated.";
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();

    } else {
        // --- INSERT new order ---
        $customer   = $conn->real_escape_string($_POST['customer']);
        $drink_id   = intval($_POST['drink_id']);
        $topping_id = !empty($_POST['topping_id']) ? intval($_POST['topping_id']) : null;
        $temperature= $conn->real_escape_string($_POST['temperature']);
        $placed_by  = $_SESSION['eid'];

        $stmt = $conn->prepare("
          INSERT INTO orders
            (customer_name, drink_id, topping_id, temperature, placed_by)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('siisi',
            $customer,
            $drink_id,
            $topping_id,
            $temperature,
            $placed_by
        );
        if ($stmt->execute()) {
            $message = "New order placed (ID: {$stmt->insert_id}).";
        } else {
            $message = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// 3) Handle GET edit request
if (isset($_GET['edit_id'])) {
    $order_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("
      SELECT customer_name, drink_id, topping_id, temperature
        FROM orders
       WHERE order_id = ?
    ");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $stmt->bind_result($customer, $drink_id, $topping_id, $temperature);
    if ($stmt->fetch()) {
        $is_edit = true;
    }
    $stmt->close();
}

// 4) Fetch dropdown options and existing orders
$drinks   = $conn->query("SELECT drink_id, name FROM drinks ORDER BY name");
$toppings = $conn->query("SELECT topping_id, name FROM toppings ORDER BY name");

$orders = $conn->query("
  SELECT
    o.order_id,
    o.customer_name,
    d.name AS drink,
    t.name AS topping,
    o.temperature
  FROM orders o
  JOIN drinks d   ON o.drink_id   = d.drink_id
  LEFT JOIN toppings t ON o.topping_id = t.topping_id
  ORDER BY o.placed_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cashier – Edit Orders</title>
  <style>
    html, body { height:100%; margin:0; }
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
      width: 600px;
      max-height: 90%;
      overflow-y: auto;
    }
    h1 { text-align:center; margin-top:0; }
    .message {
      text-align: center;
      color: green;
      margin: 10px 0;
    }
    form label, form select, form input, form button {
      display: block;
      width: 100%;
      margin: 8px 0;
      padding: 8px;
      font-size: 1em;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 6px 8px;
      text-align: left;
    }
    th { background: #d1c1aa; }
    td.actions a {
      color: #946f63;
      text-decoration: none;
    }
    .logout {
      text-align: center;
      margin-top: 20px;
    }
    .logout a { color: #946f63; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Cashier Dashboard</h1>

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <?php if ($is_edit): ?>
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
      <?php endif; ?>

      <label>
        Customer Name:
        <input type="text" name="customer"
          value="<?= htmlspecialchars($customer) ?>" required>
      </label>

      <label>
        Drink:
        <select name="drink_id" required>
          <?php while ($d = $drinks->fetch_assoc()): ?>
            <option value="<?= $d['drink_id'] ?>"
              <?= $d['drink_id'] == $drink_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </label>

      <label>
        Topping (optional):
        <select name="topping_id">
          <option value="">— None —</option>
          <?php while ($t = $toppings->fetch_assoc()): ?>
            <option value="<?= $t['topping_id'] ?>"
              <?= $t['topping_id'] == $topping_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </label>

      <label>
        Temperature:
        <select name="temperature" required>
          <option value="hot"  <?= $temperature === 'hot'  ? 'selected' : '' ?>>
            Hot
          </option>
          <option value="cold" <?= $temperature === 'cold' ? 'selected' : '' ?>>
            Cold
          </option>
        </select>
      </label>

      <button type="submit"
        name="<?= $is_edit ? 'update_order' : 'place_order' ?>">
        <?= $is_edit ? 'Update Order' : 'Place Order' ?>
      </button>

      <?php if ($is_edit): ?>
        <p style="text-align:center;margin-top:8px;">
          <a href="cashier.php">⟵ Cancel Edit</a>
        </p>
      <?php endif; ?>
    </form>

    <h2 style="margin-top:30px;">Existing Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Drink</th>
          <th>Topping</th>
          <th>Temp</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
          <tr>
            <td><?= $o['order_id'] ?></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= htmlspecialchars($o['drink']) ?></td>
            <td><?= htmlspecialchars($o['topping'] ?? '') ?></td>
            <td><?= htmlspecialchars($o['temperature']) ?></td>
            <td class="actions">
              <a href="?edit_id=<?= $o['order_id'] ?>">Edit</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <p class="logout"><a href="logout.php">Log Out</a></p>
  </div>
</body>
</html>
