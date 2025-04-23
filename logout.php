<?php
// logout.php
session_start();        // resume the session
session_unset();        // clear all session variables
session_destroy();      // destroy the session itself
header('Location: login.php');
exit;                   // stop execution
