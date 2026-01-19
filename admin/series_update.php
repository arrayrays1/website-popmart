<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /website-popmart/admin/series.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');

if ($id <= 0 || $name === '') {
  header('Location: /website-popmart/admin/series.php?error=Invalid+data');
  exit;
}

function saveImage($field, $destDir) {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
    return null;
  }
  if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
  }
  $tmp = $_FILES[$field]['tmp_name'];
  $name = basename($_FILES[$field]['name']);
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allowed = ['png','jpg','jpeg','webp'];
  if (!in_array($ext, $allowed, true)) {
    return null;
  }
  $safe = preg_replace('/[^a-zA-Z0-9-_\\.]/', '_', pathinfo($name, PATHINFO_FILENAME));
  $final = $safe . '_' . time() . '.' . $ext;
  $destPathFs = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $final;
  if (!move_uploaded_file($tmp, $destPathFs)) {
    return null;
  }
  return '/website-popmart/uploads/series/' . $final;
}

$newImg = saveImage('image', __DIR__ . '/../uploads/series');

try {
  if ($newImg) {
    $stmt = $pdo->prepare("UPDATE series SET name = ?, description = ?, image_path = ? WHERE id = ?");
    $stmt->execute([$name, $desc ?: null, $newImg, $id]);
  } else {
    $stmt = $pdo->prepare("UPDATE series SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $desc ?: null, $id]);
  }
} catch (PDOException $e) {
  $msg = 'Error+updating+series';
  if ((int)$e->errorInfo[1] === 1062) { $msg = 'Series+name+already+exists'; }
  header('Location: /website-popmart/admin/series.php?error=' . $msg);
  exit;
}

header('Location: /website-popmart/admin/series.php');