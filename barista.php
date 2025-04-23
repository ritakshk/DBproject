<?php
// barista.php
session_start();
require_once 'config.php';

// Step 6: Protect page—only logged-in baristas allowed
if (!isset($_SESSION['eid']) || $_SESSION['role'] !== 'barista') {
    header('Location: login.php');
    exit;
}

// Step 8: Handle “Complete” action which deletes the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $oid = intval($_POST['order_id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->bind_param('i', $oid);
    $stmt->execute();
    $stmt->close();
    header('Location: barista.php');
    exit;
}

// Fetch all pending orders, with drink + topping details
$sql = "
  SELECT 
    o.order_id,
    o.customer_name,
    o.temperature,
    d.name       AS drink,
    d.ingredients,
    t.name       AS topping
  FROM orders o
  JOIN drinks d   ON o.drink_id = d.drink_id
  LEFT JOIN toppings t ON o.topping_id = t.topping_id
  ORDER BY o.placed_at ASC
";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Barista – Orders</title>
  <style>
    body { font-family: Georgia, serif; background: #dfd8cf; padding: 30px; }
    .card {
      background: #ece7e0; border-radius: 10px; padding: 20px;
      margin-bottom: 20px; box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
    }
    .card h2 { margin-top: 0; }
    form { margin-top: 10px; }
    button { padding: 8px 12px; font-size: 1em; }
  </style>
</head>
<body>
  <h1>Barista Dashboard</h1>

  <?php if ($orders->num_rows === 0): ?>
    <p>No pending orders.</p>
  <?php else: ?>
    <?php while ($o = $orders->fetch_assoc()): ?>
      <div class="card">
        <h2>Order #<?= $o['order_id'] ?></h2>
        <p><strong>Customer:</strong> <?= htmlspecialchars($o['customer_name']) ?></p>
        <p><strong>Drink:</strong> <?= htmlspecialchars($o['drink']) ?></p>
        <p><strong>Ingredients:</strong> <?= htmlspecialchars($o['ingredients']) ?></p>
        <?php if ($o['topping']): ?>
          <p><strong>Topping:</strong> <?= htmlspecialchars($o['topping']) ?></p>
        <?php endif; ?>
        <p><strong>Temp:</strong> <?= htmlspecialchars($o['temperature']) ?></p>

        <form method="post">
          <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
          <button type="submit">Mark as Completed</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <p><a href="logout.php">Log Out</a></p>
</body>
</html>

