<?php
require_once '../../config/db_config.php';
require_once '../../config/security.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $search = sanitizeInput($_GET['q'] ?? '');
        $category = intval($_GET['category'] ?? 0);
        $min_price = floatval($_GET['min_price'] ?? 0);
        $max_price = floatval($_GET['max_price'] ?? 999999);
        $size = sanitizeInput($_GET['size'] ?? '');
        $color = sanitizeInput($_GET['color'] ?? '');
        $sort = sanitizeInput($_GET['sort'] ?? 'latest');
        $page = intval($_GET['page'] ?? 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $query = 'SELECT * FROM products WHERE is_active = 1 AND price BETWEEN ? AND ?';
        $params = [$min_price, $max_price];

        if ($search) {
            $query .= ' AND (name LIKE ? OR MATCH(name, short_description) AGAINST(? IN BOOLEAN MODE))';
            $params[] = '%' . $search . '%';
            $params[] = $search;
        }

        if ($category > 0) {
            $query .= ' AND category_id = ?';
            $params[] = $category;
        }

        switch ($sort) {
            case 'price_low':
                $query .= ' ORDER BY price ASC';
                break;
            case 'price_high':
                $query .= ' ORDER BY price DESC';
                break;
            case 'rating':
                $query .= ' ORDER BY rating DESC';
                break;
            case 'best_seller':
                $query .= ' ORDER BY is_best_seller DESC';
                break;
            default:
                $query .= ' ORDER BY created_at DESC';
        }

        $query .= ' LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        jsonResponse('success', 'Products retrieved', $products);
    }
} catch (Exception $e) {
    jsonResponse('error', $e->getMessage());
}
?>
