<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to submit a review']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$reviewText = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if (!$productId || !$orderId) {
    echo json_encode(['success' => false, 'message' => 'Product ID and Order ID required']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT oi.id FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
        LIMIT 1
    ");
    $stmt->execute([$orderId, $userId, $productId]);
    $validItem = $stmt->fetchColumn();

    if (!$validItem) {
        echo json_encode(['success' => false, 'message' => 'Invalid order or product.']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
    $stmt->execute([$userId, $productId, $orderId]);
    $existingReview = $stmt->fetchColumn();
    
    if ($existingReview) {
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET rating = ?, review_text = ?, updated_at = NOW()
            WHERE user_id = ? AND product_id = ? AND order_id = ?
        ");
        $success = $stmt->execute([$rating, $reviewText, $userId, $productId, $orderId]);
        $message = 'Review updated successfully';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (product_id, user_id, order_id, rating, review_text)
            VALUES (?, ?, ?, ?, ?)
        ");
        $success = $stmt->execute([$productId, $userId, $orderId, $rating, $reviewText]);
        $message = 'Review submitted successfully';
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
