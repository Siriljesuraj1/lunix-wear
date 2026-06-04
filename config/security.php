<?php
/**
 * LUNIX WEAR - Security Functions
 * CSRF Protection, Input Validation, XSS Prevention
 */

session_start();

// CSRF Token Generation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        throw new Exception('CSRF token validation failed');
    }
}

// XSS Protection - Escape HTML
function escapeHTML($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Input Sanitization
function sanitizeInput($data) {
    return trim(stripslashes($data));
}

// Password Hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify Password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Validate Email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate Phone Number (India)
function isValidPhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

// Generate Random String
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Check User Role Access
function checkRoleAccess($requiredRole) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roles = ['super_admin' => 3, 'admin' => 2, 'customer' => 1];
    $userRoleLevel = $roles[$_SESSION['user_role']] ?? 0;
    $requiredRoleLevel = $roles[$requiredRole] ?? 0;
    
    return $userRoleLevel >= $requiredRoleLevel;
}

// Redirect if unauthorized
function redirectIfUnauthorized($requiredRole = 'customer') {
    if (!checkRoleAccess($requiredRole)) {
        header('Location: /login.php');
        exit;
    }
}

// Log Activity
function logActivity($userId, $action, $details = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// JSON Response Helper
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

?>