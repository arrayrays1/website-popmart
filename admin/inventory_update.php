<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

function computeInventoryStatus(int $qty): string {
  if ($qty <= 0) return 'Out of Stock';
  if ($qty <= 10) return 'Low Stock';
  return 'In Stock';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /website-popmart/admin/inventory.php'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
if ($id <= 0 || $stock < 0) { header('Location: /website-popmart/admin/inventory.php'); exit; }

try {
  $pdo->beginTransaction();
  $stmt = $pdo->prepare("UPDATE products SET stock = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([$stock, $id]);

  // mirror inventory table if exists
  try {
    $status = computeInventoryStatus($stock);
    $upd = $pdo->prepare("UPDATE inventory SET stock_quantity = ?, status = ? WHERE product_id = ?");
    $upd->execute([$stock, $status, $id]);
    if ($upd->rowCount() === 0) {
      $ins = $pdo->prepare("INSERT INTO inventory (product_id, stock_quantity, status) VALUES (?,?,?)");
      $ins->execute([$id, $stock, $status]);
    }
  } catch (PDOException $e) { /* ignore if table missing */ }

  $pdo->commit();
  header('Location: /website-popmart/admin/inventory.php');
} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  die('DB error: ' . htmlspecialchars($e->getMessage()));
}
