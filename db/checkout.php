<?php
ob_start();
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }
require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];

$selectedItems = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
$shippingAddress = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';

if (empty($selectedItems)) {
    echo json_encode(['success'=>false,'message'=>'No items selected']); exit;
}

if (empty($paymentMethod)) {
    echo json_encode(['success'=>false,'message'=>'Payment method is required']); exit;
}

if (empty($shippingAddress)) {
    echo json_encode(['success'=>false,'message'=>'Shipping address is required']); exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? AND status='open' LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        echo json_encode(['success'=>false,'message'=>'No open cart found']); exit;
    }

    $productIds = array_column($selectedItems, 'product_id');
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[$row['id']] = $row;
    }

    $orderItems = [];
    $subtotal = 0.0;
    $insufficient = [];
    
    foreach ($selectedItems as $item) {
        $productId = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        
        if (!isset($products[$productId])) {
            $insufficient[] = 'Product ID ' . $productId . ' not found';
            continue;
        }
        
        $product = $products[$productId];
        
        if ($product['stock'] < $quantity) {
            $insufficient[] = $product['name'] . ' (available: ' . $product['stock'] . ', requested: ' . $quantity . ')';
            continue;
        }
        
        $itemTotal = $product['price'] * $quantity;
        $subtotal += $itemTotal;
        
        $orderItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $product['price']
        ];
    }

    if (!empty($insufficient)) {
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'Insufficient stock for: ' . implode(', ', $insufficient)]);
        exit;
    }

    if (empty($orderItems)) {
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'No valid items to checkout']);
        exit;
    }

    $shippingFee = 5.00;
    $total = $subtotal + $shippingFee;

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, cart_id, payment_method, shipping_address, subtotal, shipping_fee, total, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->execute([$userId, $cartId, $paymentMethod, $shippingAddress, $subtotal, $shippingFee, $total]);
    $orderId = $pdo->lastInsertId();

    $orderItemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $removeCartItemStmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
    
    foreach ($orderItems as $item) {
        $orderItemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['unit_price']]);
        
        $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        
        $removeCartItemStmt->execute([$cartId, $item['product_id']]);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $remainingItems = $stmt->fetchColumn();
    
    if ($remainingItems == 0) {
        $stmt = $pdo->prepare("UPDATE carts SET status = 'ordered' WHERE id = ?");
        $stmt->execute([$cartId]);
    }

    $pdo->commit();

    ob_end_clean();
    echo json_encode(['success'=>true,'message'=>'Order placed successfully', 'order_id' => $orderId]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_end_clean();
    $message = 'Database error: ' . $e->getMessage();
    echo json_encode(['success'=>false,'message'=>$message]);
}
?>
