<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
  } else {
    header('Location: /website-popmart/admin/queries.php');
    exit;
  }
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid query ID']);
    exit;
  } else {
    header('Location: /website-popmart/admin/queries.php');
    exit;
  }
}

try {
  $stmt = $pdo->prepare("UPDATE customer_queries SET replied_at = NOW() WHERE id = ?");
  $stmt->execute([$id]);

  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
  } else {
    header('Location: /website-popmart/admin/queries.php?success=1');
  }
} catch (PDOException $e) {
  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
  } else {
    $msg = urlencode('Error: ' . $e->getMessage());
    header('Location: /website-popmart/admin/queries.php?error=' . $msg);
  }
}
