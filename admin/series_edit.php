<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /website-popmart/admin/series.php'); exit; }

$stmt = $pdo->prepare('SELECT id, name, description, image_path, created_at FROM series WHERE id = ?');
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { header('Location: /website-popmart/admin/series.php'); exit; }
// load all series for side list (match series.php UI)
$allSeries = [];
try {
  $allSeries = $pdo->query('SELECT id, name, description, image_path, created_at FROM series ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $allSeries = []; }
?>
<?php $activePage = 'series'; require_once __DIR__ . '/includes/header.php'; ?>
  <style>
    .series-edit-page { padding:20px 32px; font-family: Arial, sans-serif; }
    .series-edit-page .grid { display:grid; grid-template-columns: 1.2fr 1fr; gap:20px; align-items:start; }
    @media (max-width: 992px) { .series-edit-page { padding:16px; } .series-edit-page .grid { grid-template-columns: 1fr; } }
    .series-edit-page .card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; }
    .series-edit-page .field { margin-bottom:12px; }
    .series-edit-page label { display:block; font-weight:600; margin-bottom:6px; }
    .series-edit-page input[type="text"], .series-edit-page textarea { width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; }
    .series-edit-page .btn { background:#111827; color:#fff; border:0; border-radius:8px; padding:8px 12px; cursor:pointer; }
    .series-edit-page .btn:hover { background:#0b0f1a; }
    .series-edit-page .thumb { height:60px; }
    .series-edit-page .thumb-table { height:40px; width:auto; border-radius:6px; border:1px solid #e5e7eb; }
    .series-edit-page .table { width:100%; border-collapse:collapse; }
    .series-edit-page .table th, .series-edit-page .table td { padding:10px; border-bottom:1px solid #e5e7eb; text-align:left; }
    .series-edit-page .muted { color:#6b7280; font-size:14px; }
    .series-edit-page .btn-danger { background:#dc2626; }
  </style>
  <div class="series-edit-page">
    <h1>Edit Series</h1>
    <div class="grid">
      <form class="card" method="post" action="/website-popmart/admin/series_update.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>" />
        <div class="field">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($s['name']); ?>" />
        </div>
        <div class="field">
          <label for="description">Description (optional)</label>
          <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($s['description'] ?? ''); ?></textarea>
        </div>
        <div class="field">
          <label for="image">Banner/Image (optional)</label>
          <input type="file" id="image" name="image" accept="image/*" />
          <?php if (!empty($s['image_path'])): ?>
            <div style="margin-top:8px;"><img class="thumb" src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="" /></div>
          <?php endif; ?>
        </div>
        <div>
          <button class="btn" type="submit">Save</button>
          <a class="btn" href="/website-popmart/admin/series.php" style="background:#6b7280;">Cancel</a>
        </div>
      </form>
      <div class="card">
        <h3>All Series</h3>
        <?php if ($allSeries && count($allSeries) > 0): ?>
          <table class="table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allSeries as $row): ?>
                <tr>
                  <td><?php if (!empty($row['image_path'])): ?><img class="thumb-table" src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="" /><?php endif; ?></td>
                  <td style="min-width:160px;">&nbsp;<?php echo htmlspecialchars($row['name']); ?></td>
                  <td class="muted" style="max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">&nbsp;<?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                  <td class="muted"><?php echo htmlspecialchars($row['created_at']); ?></td>
                  <td>
                    <a class="btn" href="/website-popmart/admin/series_edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                    <form method="post" action="/website-popmart/admin/series_delete.php" onsubmit="return confirm('Delete this series?');" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>" />
                      <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="muted">No series yet. Add one on the left.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
