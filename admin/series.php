<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

// fetch all series
$series = [];
try {
  $hasSeries = $pdo->query("SHOW TABLES LIKE 'series'")->fetch();
  if ($hasSeries) {
    $series = $pdo->query("SELECT id, name, description, image_path, created_at FROM series ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (PDOException $e) { /* ignore */ }
?>
<?php $activePage = 'series'; require_once __DIR__ . '/includes/header.php'; ?>
<style>
  .series-page { padding:20px; font-family: Arial, sans-serif; }
  .series-page .grid { display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items:start; }
  .series-page .card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; }
  .series-page table { width:100%; border-collapse: collapse; }
  .series-page th, .series-page td { border-bottom:1px solid #e5e7eb; padding:8px; text-align:left; vertical-align:top; }
  .series-page .btn { background:#111827; color:#fff; border:0; border-radius:8px; padding:8px 12px; cursor:pointer; }
  .series-page .btn:hover { background:#0b0f1a; }
  .series-page .btn-danger { background:#dc2626; }
  .series-page .thumb { height:40px; width:auto; border-radius:6px; border:1px solid #e5e7eb; }
  .series-page .muted { color:#6b7280; font-size:12px; }
</style>
  <div class="series-page">
    <h1>Series</h1>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger" style="margin-bottom:12px; color:#b91c1c;"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <div class="grid">
      <div class="card">
        <h3>Add Series</h3>
        <form method="post" action="/website-popmart/admin/series_save.php" enctype="multipart/form-data">
          <div class="field" style="margin-bottom:10px;">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;" />
          </div>
          <div class="field" style="margin-bottom:10px;">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" rows="3" style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;"></textarea>
          </div>
          <div class="field" style="margin-bottom:10px;">
            <label for="image">Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*" />
          </div>
          <?php if (isset($_GET['return'])): ?>
            <input type="hidden" name="return" value="<?php echo htmlspecialchars($_GET['return']); ?>" />
          <?php endif; ?>
          <button class="btn" type="submit">Save Series</button>
        </form>
      </div>
      <div class="card">
        <h3>All Series</h3>
        <?php if ($series && count($series) > 0): ?>
          <table>
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
              <?php foreach ($series as $s): ?>
                <tr>
                  <td><?php if (!empty($s['image_path'])): ?><img class="thumb" src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="" /><?php endif; ?></td>
                  <td style="min-width:160px;"><?php echo htmlspecialchars($s['name']); ?></td>
                  <td class="muted" style="max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($s['description'] ?? ''); ?></td>
                  <td class="muted"><?php echo htmlspecialchars($s['created_at']); ?></td>
                  <td>
                    <a class="btn" href="/website-popmart/admin/series_edit.php?id=<?php echo (int)$s['id']; ?>">Edit</a>
                    <form method="post" action="/website-popmart/admin/series_delete.php" onsubmit="return confirm('Delete this series?');" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>" />
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