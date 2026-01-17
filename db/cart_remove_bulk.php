<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];
$productIds = isset($_POST['product_ids']) && is_array($_POST['product_ids']) ? $_POST['product_ids'] : [];

if (empty($productIds)) {
    echo json_encode(['success' => false, 'message' => 'No items specified']);
    exit;
}

try {
    // Get cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? AND status='open'");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        echo json_encode(['success' => false, 'message' => 'Cart not found']);
        exit;
    }

    // Delete items
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    // Params: cart_id, then product_ids
    $params = array_merge([$cartId], $productIds);
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id IN ($placeholders)");
    $stmt->execute($params);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>