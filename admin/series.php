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

  <div class="row mb-4">
    <div class="col-md-8">
      <h1 class="page-title">Series Management</h1>
      <p class="page-subtitle">Manage your product series and collections</p>
    </div>
    <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
      <span class="text-muted">Total: <?php echo count($series); ?> series</span>
    </div>
  </div>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Add Series Form -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white border-0 py-3">
          <h5 class="mb-0">Add New Series</h5>
        </div>
        <div class="card-body">
          <form method="post" action="/website-popmart/admin/series_save.php" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="name" class="form-label">Series Name *</label>
              <input type="text" class="form-control" id="name" name="name" required placeholder="Enter series name">
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description"></textarea>
            </div>
            <div class="mb-3">
              <label for="image" class="form-label">Series Image</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*">
              <div class="form-text">Optional. Recommended size: 400x400px</div>
            </div>
            <?php if (isset($_GET['return'])): ?>
              <input type="hidden" name="return" value="<?php echo htmlspecialchars($_GET['return']); ?>" />
            <?php endif; ?>
            <button type="submit" class="btn btn-success w-100">
              Create Series
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Series List -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
          <h5 class="mb-0">All Series</h5>
          <span class="badge bg-primary"><?php echo count($series); ?> total</span>
        </div>
        <div class="card-body p-0">
          <?php if ($series && count($series) > 0): ?>
            <div class="table-container">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Series Name</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th class="text-center" style="width: 150px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($series as $s): ?>
                    <tr>
                      <td>
                        <?php if (!empty($s['image_path'])): ?>
                          <img src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                          <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-image text-muted"></i>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($s['name']); ?></div>
                      </td>
                      <td>
                        <small class="text-muted">
                          <?php echo !empty($s['description']) ? htmlspecialchars(substr($s['description'], 0, 60)) . (strlen($s['description']) > 60 ? '...' : '') : 'No description'; ?>
                        </small>
                      </td>
                      <td>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($s['created_at'])); ?></small>
                      </td>
                      <td class="text-center">
                        <div class="btn-group btn-group-sm">
                          <a class="btn btn-outline-primary" href="/website-popmart/admin/series_edit.php?id=<?php echo (int)$s['id']; ?>" title="Edit">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <button class="btn btn-outline-danger" onclick="deleteSeries(<?php echo (int)$s['id']; ?>, '<?php echo htmlspecialchars($s['name']); ?>')" title="Delete">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-collection" style="font-size: 3rem; color: #dee2e6;"></i>
              <h5 class="text-muted mt-3">No Series Yet</h5>
              <p class="text-muted">Create your first product series to get started</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Form (hidden) -->
  <form id="deleteForm" method="post" action="/website-popmart/admin/series_delete.php" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
  </form>

  <script>
  function deleteSeries(id, name) {
    if (confirm('Are you sure you want to delete the series "' + name + '"?\n\nThis action cannot be undone and may affect associated products.')) {
      document.getElementById('deleteId').value = id;
      document.getElementById('deleteForm').submit();
    }
  }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>