<?php
require_once '../config/db_config.php';
require_once '../config/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['csrf_token'] ?? '';
        verifyCSRFToken($token);

        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
            throw new Exception('All fields are required');
        }

        if (!isValidEmail($email)) {
            throw new Exception('Invalid email format');
        }

        if (!isValidPhone($phone)) {
            throw new Exception('Invalid phone number');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }

        $password_hash = hashPassword($password);
        $verify_token = generateRandomString();

        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, phone, password_hash, email_verify_token) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$first_name, $last_name, $email, $phone, $password_hash, $verify_token]);
        $user_id = $pdo->lastInsertId();

        logActivity($user_id, 'user_registration', 'New user registered');

        jsonResponse('success', 'Registration successful', ['user_id' => $user_id]);

    } catch (Exception $e) {
        jsonResponse('error', $e->getMessage());
    }
}
?>