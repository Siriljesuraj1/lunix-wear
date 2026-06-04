<?php
require_once '../config/security.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'user_logout', 'User logged out');
}

session_destroy();
header('Location: /index.php');
exit;
?>