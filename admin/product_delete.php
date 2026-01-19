<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /website-popmart/admin/products.php'); exit; }

try {
  $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
  $stmt->execute([$id]);
} catch (PDOException $e) {
  // foreign key restriction (product referenced in orders)
  $msg = urlencode('Cannot delete product: it may have related orders.');
  header('Location: /website-popmart/admin/products.php?error=' . $msg);
  exit;
}

header('Location: /website-popmart/admin/products.php');
