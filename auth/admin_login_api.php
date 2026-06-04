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

        $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.password_hash, u.is_active, CASE WHEN sa.id IS NOT NULL THEN 'super_admin' WHEN a.id IS NOT NULL THEN 'admin' ELSE NULL END as role FROM users u LEFT JOIN super_admins sa ON u.id = sa.user_id LEFT JOIN admins a ON u.id = a.user_id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !$user['role']) {
            throw new Exception('Invalid credentials');
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            throw new Exception('Invalid credentials');
        }

        if (!$user['is_active']) {
            throw new Exception('Account is inactive');
        }

        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['admin_email'] = $email;
        $_SESSION['user_role'] = $user['role'];

        $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$user['id']]);

        logActivity($user['id'], 'admin_login', 'Admin logged in');

        $redirect = ($user['role'] === 'super_admin') ? '/admin/super-admin/dashboard.php' : '/admin/dashboard.php';
        jsonResponse('success', 'Login successful', ['redirect' => $redirect]);

    } catch (Exception $e) {
        jsonResponse('error', $e->getMessage());
    }
}
?>