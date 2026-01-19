<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /website-popmart/admin/series.php');
  exit;
}

$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');
$return = isset($_POST['return']) ? $_POST['return'] : '';

if ($name === '') {
  header('Location: /website-popmart/admin/series.php?error=Name+is+required' . ($return ? '&return=' . urlencode($return) : ''));
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

$img = saveImage('image', __DIR__ . '/../uploads/series');

try {
  // enforce unique name; raise error if duplicate
  $stmt = $pdo->prepare("INSERT INTO series (name, description, image_path) VALUES (?,?,?)");
  $stmt->execute([$name, $desc ?: null, $img ?: null]);

  // optional: return back to originating form (e.g., product_form)
  if (!empty($return)) {
    header('Location: ' . $return);
    exit;
  }
} catch (PDOException $e) {
  $msg = 'Error+saving+series';
  if ((int)$e->errorInfo[1] === 1062) { $msg = 'Series+name+already+exists'; }
  header('Location: /website-popmart/admin/series.php?error=' . $msg . ($return ? '&return=' . urlencode($return) : ''));
  exit;
}

header('Location: /website-popmart/admin/series.php');