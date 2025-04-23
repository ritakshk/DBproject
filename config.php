<?php
// config.php
// Change the credentials below if you set a different user/password
$conn = new mysqli('localhost', 'root', '', 'boba_query');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
