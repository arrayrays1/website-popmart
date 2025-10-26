<?php
ob_start();
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }
require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];

try {
    // this will open the cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? AND status='open' LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        echo json_encode(['success'=>false,'message'=>'No open cart found']); exit;
    }

    // get the cart items with current stock based on database
    $stmt = $pdo->prepare("
        SELECT ci.product_id, ci.quantity, p.stock, p.name
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        echo json_encode(['success'=>false,'message'=>'Cart is empty']); exit;
    }

    // check how many stock are available
    $insufficient = [];
    foreach ($items as $item) {
        if ($item['stock'] < $item['quantity']) {
            $insufficient[] = $item['name'] . ' (available: ' . $item['stock'] . ', requested: ' . $item['quantity'] . ')';
        }
    }

    if (!empty($insufficient)) {
        ob_end_clean();
        echo json_encode(['success'=>false,'message'=>'Insufficient stock for: ' . implode(', ', $insufficient)]);
        exit;
    }

    // this will start the transaction
    $pdo->beginTransaction();

    // reduce the stock for each product assigned by the user
    $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    foreach ($items as $item) {
        $updateStmt->execute([$item['quantity'], $item['product_id']]);
    }

    // update the cart status to ordered
    $stmt = $pdo->prepare("UPDATE carts SET status = 'ordered' WHERE id = ?");
    $stmt->execute([$cartId]);

    // this will commit the transaction
    $pdo->commit();

    ob_end_clean();
    echo json_encode(['success'=>true,'message'=>'Checkout successful']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_end_clean();
    $message = 'Database error: ' . $e->getMessage();
    echo json_encode(['success'=>false,'message'=>$message]);
}
?>