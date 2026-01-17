<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    return ['success' => false, 'message' => 'Not logged in'];
}

require_once __DIR__ . '/db_connect.php';
$userId = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $stmtItems = $pdo->prepare("
            SELECT oi.*, p.name, p.image_path, p.id as product_id
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$order['id']]);
        $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($order); // break reference

    $stmtReviewed = $pdo->prepare("SELECT order_id, product_id FROM reviews WHERE user_id = ?");
    $stmtReviewed->execute([$userId]);
    $reviewedPairs = $stmtReviewed->fetchAll(PDO::FETCH_ASSOC);
    
    $reviewedMap = [];
    foreach ($reviewedPairs as $pair) {
        if ($pair['order_id']) {
            $reviewedMap[$pair['order_id'] . '-' . $pair['product_id']] = true;
        }
    }

    foreach ($orders as &$order) {
        foreach ($order['items'] as &$item) {
            $key = $order['id'] . '-' . $item['product_id'];
            $item['is_reviewed'] = isset($reviewedMap[$key]);
        }
    }
    unset($order); unset($item);

    return ['success' => true, 'orders' => $orders];

} catch (PDOException $e) {
    return ['success' => false, 'message' => 'Database error'];
}
?>