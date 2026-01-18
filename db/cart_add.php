<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success'=>false,'message'=>'Missing product_id']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];
$productId = (int)$_POST['product_id'];
$qty = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

try {
    // Read product price and stock
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success'=>false,'message'=>'Product not found']);
        exit;
    }
    
    $unitPrice = $product['price'];
    $stock = (int)$product['stock'];
    
    // Check if product is out of stock
    if ($stock <= 0) {
        echo json_encode(['success'=>false,'message'=>'Sorry, this product is out of stock']);
        exit;
    }
    
    // Get or create open cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? AND status = 'open' LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();
    
    if (!$cartId) {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id, status) VALUES (?, 'open')");
        $stmt->execute([$userId]);
        $cartId = $pdo->lastInsertId();
    }
    
    // Check current quantity in cart
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $currentQty = (int)$stmt->fetchColumn();
    
    // Check if adding would exceed stock
    $newTotal = $currentQty + $qty;
    if ($newTotal > $stock) {
        $canAdd = $stock - $currentQty;
        if ($canAdd <= 0) {
            echo json_encode(['success'=>false,'message'=>'You already have the maximum available quantity in your cart']);
            exit;
        }
        $qty = $canAdd; // Adjust to maximum available
    }

    // Upsert cart item
    $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), unit_price = VALUES(unit_price)");
    $success = $stmt->execute([$cartId, $productId, $qty, $unitPrice]);
    
    if ($success) {
        echo json_encode(['success'=>true,'message'=>'Added to cart']);
    } else {
        echo json_encode(['success'=>false,'message'=>'DB error']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Database error: ' . $e->getMessage()]);
}
?>


