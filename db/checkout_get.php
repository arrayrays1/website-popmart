<?php
// Note: session_start() is called in checkout.php before including this file
if (!isset($_SESSION['user_id'])) { 
    return ['error' => 'Please log in to proceed with checkout'];
}
require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];

try {
    // Get user information
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, contact_number FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return ['error' => 'User not found'];
    }
    
    // Get selected items from POST
    $selectedItems = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        // Handle array format: items[0][product_id], items[0][quantity]
        foreach ($_POST['items'] as $item) {
            if (isset($item['product_id']) && isset($item['quantity'])) {
                $selectedItems[] = [
                    'product_id' => (int)$item['product_id'],
                    'quantity' => (int)$item['quantity']
                ];
            }
        }
    }
    
    if (empty($selectedItems)) {
        return ['error' => 'No items selected. Please go back to cart and select items to checkout.'];
    }
    
    // Get product details for selected items
    $productIds = array_column($selectedItems, 'product_id');
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, image_path, price, stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[$row['id']] = $row;
    }
    
    // Build order items
    $orderItems = [];
    $subtotal = 0.0;
    foreach ($selectedItems as $item) {
        $productId = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        if (isset($products[$productId])) {
            $product = $products[$productId];
            $itemTotal = $product['price'] * $quantity;
            $subtotal += $itemTotal;
            $orderItems[] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'image_path' => $product['image_path'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'total' => $itemTotal,
                'stock' => $product['stock']
            ];
        }
    }
    
    $shippingFee = 5.00; // Fixed shipping fee
    $total = $subtotal + $shippingFee;
    
    return [
        'user' => $user,
        'items' => $orderItems,
        'subtotal' => $subtotal,
        'shipping_fee' => $shippingFee,
        'total' => $total,
        'item_count' => array_sum(array_column($orderItems, 'quantity'))
    ];
    
} catch (PDOException $e) {
    error_log("Checkout get error: " . $e->getMessage());
    return ['error' => 'Database error'];
}
?>
