<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($name === '' || $email === '' || $message === '') {
    echo json_encode(['success'=>false,'message'=>'All fields are required.']);
    exit;
}

function generateUniqueQueryId($pdo) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $queryId = '';
        for ($i = 0; $i < 6; $i++) {
            $queryId .= $characters[rand(0, strlen($characters) - 1)];
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer_queries WHERE query_id = ?");
        $stmt->execute([$queryId]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);
    return $queryId;
}

try {
    $isLoggedIn = isset($_SESSION['user_id']);
    $userId = $isLoggedIn ? (int)$_SESSION['user_id'] : null;

    if ($isLoggedIn && $userId) {
        $queryId = generateUniqueQueryId($pdo);
        $stmt = $pdo->prepare('INSERT INTO customer_queries (query_id, name, email, message, is_registered, user_id) VALUES (?, ?, ?, ?, 1, ?)');
        $ok = $stmt->execute([$queryId, $name, $email, $message, $userId]);
        $table = 'customer_queries';
    } else {
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
        $ok = $stmt->execute([$name, $email, $message]);
        $table = 'contact_messages';
    }

    echo json_encode(['success'=>$ok, 'table'=>$table]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Database error: ' . $e->getMessage()]);
}
?>


