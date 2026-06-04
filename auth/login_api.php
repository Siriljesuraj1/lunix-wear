<?php
require_once '../config/db_config.php';
require_once '../config/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['csrf_token'] ?? '';
        verifyCSRFToken($token);

        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception('Email and password required');
        }

        $stmt = $pdo->prepare('SELECT id, first_name, last_name, password_hash, is_active FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !verifyPassword($password, $user['password_hash'])) {
            throw new Exception('Invalid credentials');
        }

        if (!$user['is_active']) {
            throw new Exception('Account is inactive');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'customer';

        $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$user['id']]);

        logActivity($user['id'], 'user_login', 'User logged in');

        jsonResponse('success', 'Login successful', ['redirect' => '/']);

    } catch (Exception $e) {
        jsonResponse('error', $e->getMessage());
    }
}
?>