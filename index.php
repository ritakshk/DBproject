<?php
// index.php
session_start();

// If already logged in, send them to their dashboard
if (isset($_SESSION['eid']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'cashier') {
        header('Location: cashier.php');
    } else {
        header('Location: barista.php');
    }
    exit;
}

// Otherwise, send them to log in
header('Location: login.php');
exit;

