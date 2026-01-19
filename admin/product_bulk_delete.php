<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /website-popmart/admin/products.php');
  exit;
}

$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || count($ids) === 0) {
  header('Location: /website-popmart/admin/products.php?error=No+products+selected');
  exit;
}

// sanitize to integers and remove invalids
$ids = array_values(array_filter(array_map('intval', $ids), function($v){ return $v > 0; }));
if (count($ids) === 0) {
  header('Location: /website-popmart/admin/products.php?error=No+valid+product+IDs');
  exit;
}

try {
  // determine which selected products are referenced (used in orders or cart) and should not be deleted
  $in = implode(',', array_fill(0, count($ids), '?'));
  $blocked = [];
  // order_items references
  try {
    $q = $pdo->prepare("SELECT product_id FROM order_items WHERE product_id IN ($in) GROUP BY product_id");
    $q->execute($ids);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $pid) { $blocked[(int)$pid] = true; }
  } catch (PDOException $e) { /* ignore */ }
  // cart_items references
  try {
    $q = $pdo->prepare("SELECT product_id FROM cart_items WHERE product_id IN ($in) GROUP BY product_id");
    $q->execute($ids);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $pid) { $blocked[(int)$pid] = true; }
  } catch (PDOException $e) { /* ignore */ }

  $deletable = array_values(array_filter($ids, function($pid) use ($blocked){ return !isset($blocked[$pid]); }));
  $blockedCount = count($ids) - count($deletable);

  if (count($deletable) === 0) {
    header('Location: /website-popmart/admin/products.php?error=Selected+products+cannot+be+deleted+(in+use)&blocked=' . $blockedCount);
    exit;
  }

  $pdo->beginTransaction();
  // delete inventory rows first (best-effort)
  try {
    $inDel = implode(',', array_fill(0, count($deletable), '?'));
    $stmtInv = $pdo->prepare("DELETE FROM inventory WHERE product_id IN ($inDel)");
    $stmtInv->execute($deletable);
  } catch (PDOException $e) { /* ignore */ }

  // delete products
  $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($inDel)");
  $stmt->execute($deletable);

  $pdo->commit();
  $deletedCount = count($deletable);
  $qs = 'deleted=' . $deletedCount;
  if ($blockedCount > 0) { $qs .= '&blocked=' . $blockedCount; }
  header('Location: /website-popmart/admin/products.php?' . $qs);
  exit;
} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  header('Location: /website-popmart/admin/products.php?error=Bulk+delete+failed');
  exit;
}
