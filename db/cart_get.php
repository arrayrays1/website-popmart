<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /website-popmart/index.php'); exit; }
require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];

try {
    // get or create open cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id=? AND status='open' LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();
    
    if (!$cartId) {
        // create one if none
        $stmt = $pdo->prepare("INSERT INTO carts (user_id, status) VALUES (?, 'open')");
        $stmt->execute([$userId]);
        $cartId = $pdo->lastInsertId();
    }

    // get cart items with stock information
    $stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, ci.unit_price, p.name, p.image_path, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ? ORDER BY ci.id DESC");
    $stmt->execute([$cartId]);
    $rows = $stmt->fetchAll();

    $items = [];
    $subtotal = 0.0;
    foreach ($rows as $row) {
        $row['total'] = $row['quantity'] * $row['unit_price'];
        $subtotal += $row['total'];
        $items[] = $row;
    }

    return [ 'items' => $items, 'subtotal' => $subtotal, 'count' => count($items) ];
    
} catch (PDOException $e) {
    error_log("Cart get error: " . $e->getMessage());
    return [ 'items' => [], 'subtotal' => 0.0, 'count' => 0 ];
}
?>


