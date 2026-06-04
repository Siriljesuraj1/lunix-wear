<?php
require_once '../../config/db_config.php';
require_once '../../config/security.php';

redirectIfUnauthorized('admin');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['csrf_token'] ?? '';
        verifyCSRFToken($token);

        $shipping_address_id = intval($_POST['shipping_address_id'] ?? 0);
        $billing_address_id = intval($_POST['billing_address_id'] ?? 0);
        $payment_method = sanitizeInput($_POST['payment_method'] ?? 'cod');
        $coupon_code = sanitizeInput($_POST['coupon_code'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;

        if (!$user_id) {
            throw new Exception('Please login to place order');
        }

        $stmt = $pdo->prepare('SELECT * FROM cart WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        if (empty($cart_items)) {
            throw new Exception('Cart is empty');
        }

        $subtotal = 0;
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $subtotal += $price * $item['quantity'];
        }

        $discount = 0;
        if (!empty($coupon_code)) {
            $stmt = $pdo->prepare('SELECT discount_type, discount_value FROM coupons WHERE code = ? AND valid_from <= CURDATE() AND valid_until >= CURDATE()');
            $stmt->execute([$coupon_code]);
            $coupon = $stmt->fetch();

            if ($coupon) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discount = ($subtotal * $coupon['discount_value']) / 100;
                } else {
                    $discount = $coupon['discount_value'];
                }
            }
        }

        $shipping_cost = 100;
        $tax_rate = 0.18;
        $tax_amount = ($subtotal - $discount) * $tax_rate;
        $total_amount = $subtotal - $discount + $shipping_cost + $tax_amount;

        $order_number = 'ORD' . date('Ymd') . rand(10000, 99999);

        $stmt = $pdo->prepare('INSERT INTO orders (order_number, user_id, status, payment_method, subtotal, shipping_cost, tax_amount, discount_amount, total_amount, coupon_code, shipping_address_id, billing_address_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$order_number, $user_id, 'pending', $payment_method, $subtotal, $shipping_cost, $tax_amount, $discount, $total_amount, $coupon_code ?: null, $shipping_address_id, $billing_address_id]);

        $order_id = $pdo->lastInsertId();

        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, size, color, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['size'], $item['color'], $price, $price * $item['quantity']]);

            $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
        $stmt->execute([$user_id]);

        logActivity($user_id, 'order_placed', 'Order: ' . $order_number);
        jsonResponse('success', 'Order placed successfully', ['order_id' => $order_id, 'order_number' => $order_number, 'total' => $total_amount]);

    } catch (Exception $e) {
        jsonResponse('error', $e->getMessage());
    }
}
?>
