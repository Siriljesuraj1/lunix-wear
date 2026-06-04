<?php
require_once '../../config/db_config.php';
require_once '../../config/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['csrf_token'] ?? '';
        verifyCSRFToken($token);

        $company_name = sanitizeInput($_POST['company_name'] ?? '');
        $contact_person = sanitizeInput($_POST['contact_person'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $product_type = sanitizeInput($_POST['product_type'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $delivery_date = sanitizeInput($_POST['delivery_date'] ?? '');
        $special_requirements = sanitizeInput($_POST['special_requirements'] ?? '');

        if (empty($company_name) || empty($contact_person) || empty($phone) || empty($email) || $quantity <= 0) {
            throw new Exception('All fields required');
        }

        if (!isValidEmail($email) || !isValidPhone($phone)) {
            throw new Exception('Invalid email or phone');
        }

        $logo_file = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
            $allowed = ['png', 'jpg', 'jpeg', 'svg', 'pdf'];
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid logo type');
            }

            $upload_dir = '../../uploads/bulk_orders/';\n            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = 'bulk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filepath = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                throw new Exception('Logo upload failed');
            }

            $logo_file = '/uploads/bulk_orders/' . $filename;
        }

        $order_number = 'BULK' . date('Ymd') . rand(10000, 99999);

        $stmt = $pdo->prepare('INSERT INTO bulk_orders (order_number, company_name, contact_person, phone, email, product_type, quantity, logo_file, delivery_date, special_requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$order_number, $company_name, $contact_person, $phone, $email, $product_type, $quantity, $logo_file, $delivery_date, $special_requirements]);

        jsonResponse('success', 'Bulk order inquiry submitted', ['order_number' => $order_number]);

    } catch (Exception $e) {
        jsonResponse('error', $e->getMessage());
    }
}
?>
