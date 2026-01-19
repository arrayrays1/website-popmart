<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /website-popmart/admin/queries.php'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) { header('Location: /website-popmart/admin/queries.php'); exit; }

try {
  $stmt = $pdo->prepare("UPDATE customer_queries SET replied_at = NOW() WHERE id = ?");
  $stmt->execute([$id]);
  header('Location: /website-popmart/admin/queries.php');
} catch (PDOException $e) {
  $msg = urlencode('Error: ' . $e->getMessage());
  header('Location: /website-popmart/admin/queries.php?error=' . $msg);
}
