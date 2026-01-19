<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

// helpers
function saveUploadedImage($field, $destDir) {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
    return null; // no new file
  }
  if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
  }
  $tmp = $_FILES[$field]['tmp_name'];
  $name = basename($_FILES[$field]['name']);
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allowed = ['png','jpg','jpeg','webp'];
  if (!in_array($ext, $allowed, true)) {
    throw new Exception('Invalid image type');
  }
  $safe = preg_replace('/[^a-zA-Z0-9-_\.]/','_', pathinfo($name, PATHINFO_FILENAME));
  $final = $safe . '_' . time() . '.' . $ext;
  $destPathFs = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $final;
  if (!move_uploaded_file($tmp, $destPathFs)) {
    throw new Exception('Failed to move uploaded file');
  }
  // return web path
  $webBase = '/website-popmart/uploads/products/' . $final;
  return $webBase;
}

function computeInventoryStatus(int $qty): string {
  if ($qty <= 0) return 'Out of Stock';
  if ($qty <= 10) return 'Low Stock';
  return 'In Stock';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /website-popmart/admin/products.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$series_id = isset($_POST['series_id']) ? (int)$_POST['series_id'] : 0; // normalized FK
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($name === '' || $series_id <= 0 || $price < 0 || $stock < 0) {
  header('Location: /website-popmart/admin/product_form.php?id=' . $id);
  exit;
}

try {
  $pdo->beginTransaction();
  $imgPath = null;
  try {
    $imgPath = saveUploadedImage('image', __DIR__ . '/../uploads/products');
  } catch (Exception $e) {
    // if invalid image, rollback and show simple error
    $pdo->rollBack();
    die('Image upload error: ' . htmlspecialchars($e->getMessage()));
  }

  if ($id > 0) {
    // update existing
    if ($imgPath) {
      $stmt = $pdo->prepare("UPDATE products SET name=?, series_id=?, description=?, price=?, image_path=?, stock=?, updated_at=NOW() WHERE id=?");
      $stmt->execute([$name, $series_id, $description, $price, $imgPath, $stock, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE products SET name=?, series_id=?, description=?, price=?, stock=?, updated_at=NOW() WHERE id=?");
      $stmt->execute([$name, $series_id, $description, $price, $stock, $id]);
    }
  } else {
    // insert new
    $stmt = $pdo->prepare("INSERT INTO products (name, series_id, description, image_path, price, stock, created_at, updated_at) VALUES (?,?,?,?,?, ?,NOW(),NOW())");
    $stmt->execute([$name, $series_id, $description, $imgPath ?? '', $price, $stock]);
    $id = (int)$pdo->lastInsertId();
  }

  // upsert inventory mirror
  try {
    $status = computeInventoryStatus($stock);
    // try update, if none affected then insert
    $upd = $pdo->prepare("UPDATE inventory SET stock_quantity = ?, status = ? WHERE product_id = ?");
    $upd->execute([$stock, $status, $id]);
    if ($upd->rowCount() === 0) {
      $ins = $pdo->prepare("INSERT INTO inventory (product_id, stock_quantity, status) VALUES (?,?,?)");
      $ins->execute([$id, $stock, $status]);
    }
  } catch (PDOException $e) {
    // ignore if inventory table not present; admin_update.php creates it
  }

  $pdo->commit();
  header('Location: /website-popmart/admin/products.php');
} catch (PDOException $e) {
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  die('DB error: ' . htmlspecialchars($e->getMessage()));
}
