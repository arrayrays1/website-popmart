<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.review_text, r.created_at, 
               u.first_name, u.last_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
        FROM reviews
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $avgRating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
    $totalReviews = (int)$stats['total_reviews'];

    $canReview = false;
    $userReview = null;
    $purchaseMessage = 'Please log in to review.';

    if ($userId) {
        $stmt = $pdo->prepare("
            SELECT o.id FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status != 'cancelled'
            LIMIT 1
        ");
        $stmt->execute([$userId, $productId]);
        $hasPurchased = $stmt->fetchColumn();

        if ($hasPurchased) {
            $canReview = true;
            $purchaseMessage = '';
            
            $stmt = $pdo->prepare("SELECT id, rating, review_text FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $userReview = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $purchaseMessage = 'You must purchase this item to review it.';
        }
    }
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'avg_rating' => $avgRating,
        'total_reviews' => $totalReviews,
        'can_review' => $canReview,
        'user_review' => $userReview,
        'purchase_message' => $purchaseMessage,
        'is_logged_in' => ($userId > 0)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
