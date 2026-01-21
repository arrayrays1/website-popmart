<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$next = isset($_POST['next_status']) ? trim($_POST['next_status']) : '';

if ($orderId <= 0 || $next === '') {
  echo json_encode(['success' => false, 'message' => 'Missing params']);
  exit;
}

$allowed = [
  'Pending' => 'To Ship',
  'To Ship' => 'To Deliver',
  'To Deliver' => 'Delivered'
];

try {
  $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
  $stmt->execute([$orderId]);
  $current = $stmt->fetchColumn();
  if (!$current) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
  }
  if (!isset($allowed[$current]) || $allowed[$current] !== $next) {
    echo json_encode(['success' => false, 'message' => 'Invalid transition']);
    exit;
  }

  $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
  $upd->execute([$next, $orderId]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
