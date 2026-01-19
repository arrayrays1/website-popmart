<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /website-popmart/admin/series.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  header('Location: /website-popmart/admin/series.php');
  exit;
}

try {
  // guard: do not allow delete if any products reference this series
  $count = 0;
  try {
    $q = $pdo->prepare('SELECT COUNT(*) FROM products WHERE series_id = ?');
    $q->execute([$id]);
    $count = (int)$q->fetchColumn();
  } catch (PDOException $e) { /* ignore */ }

  if ($count > 0) {
    // redirect back with simple message via query string (no session flash system present)
    header('Location: /website-popmart/admin/series.php?error=Series+in+use+by+products');
    exit;
  }

  $stmt = $pdo->prepare('DELETE FROM series WHERE id = ?');
  $stmt->execute([$id]);
} catch (PDOException $e) {
  // ignore
}

header('Location: /website-popmart/admin/series.php');
