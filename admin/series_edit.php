<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: /website-popmart/admin/series.php'); exit; }

$stmt = $pdo->prepare('SELECT id, name, description, image_path, created_at FROM series WHERE id = ?');
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { header('Location: /website-popmart/admin/series.php'); exit; }

// Get product count for this series
$productCount = 0;
try {
  $countStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE series_id = ?');
  $countStmt->execute([$id]);
  $productCount = $countStmt->fetchColumn();
} catch (Exception $e) { /* ignore */ }
?>
<?php $activePage = 'series'; require_once __DIR__ . '/includes/header.php'; ?>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="page-title">Edit Series</h1>
      <p class="page-subtitle">Update series information and settings</p>
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
    <!-- Edit Form -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Series Information</h5>
        </div>
        <div class="card-body">
          <form method="post" action="/website-popmart/admin/series_update.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>" />

            <div class="row mb-3">
              <div class="col-md-8">
                <label for="name" class="form-label">Series Name *</label>
                <input type="text" class="form-control" id="name" name="name" required
                       value="<?php echo htmlspecialchars($s['name']); ?>" placeholder="Enter series name">
              </div>
              <div class="col-md-4">
                <label class="form-label">Products in Series</label>
                <div class="form-control-plaintext">
                  <span class="badge bg-info fs-6"><?php echo $productCount; ?> products</span>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="4"
                        placeholder="Optional description for the series"><?php echo htmlspecialchars($s['description'] ?? ''); ?></textarea>
              <div class="form-text">Provide additional information about this product series.</div>
            </div>

            <div class="mb-3">
              <label for="image" class="form-label">Series Image</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*">
              <div class="form-text">Upload a new image to replace the current one. Recommended size: 400x400px</div>

              <?php if (!empty($s['image_path'])): ?>
                <div class="mt-3">
                  <label class="form-label">Current Image:</label>
                  <div class="border rounded p-2 bg-light">
                    <img src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="Current series image"
                         class="img-fluid rounded" style="max-height: 150px;">
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="mb-3">
              <label class="form-label">Created</label>
              <div class="form-control-plaintext">
                <small class="text-muted"><?php echo date('F d, Y \a\t H:i', strtotime($s['created_at'])); ?></small>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                Update Series
              </button>
              <a href="/website-popmart/admin/series.php" class="btn btn-secondary">
                Back to Series
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Series Stats -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-white border-0">
          <h6 class="mb-0">Series Overview</h6>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-12 mb-3">
              <div class="border rounded p-3">
                <h4 class="text-primary mb-1"><?php echo $productCount; ?></h4>
                <small class="text-muted">Total Products</small>
              </div>
            </div>
            <div class="col-12">
              <div class="border rounded p-3">
                <h6 class="text-info mb-1"><?php echo htmlspecialchars($s['name']); ?></h6>
                <small class="text-muted">Series Name</small>
              </div>
            </div>
          </div>

          <?php if ($productCount > 0): ?>
            <div class="mt-3">
              <a href="/website-popmart/products-tab/products-series.php?series_id=<?php echo (int)$s['id']; ?>"
                 class="btn btn-outline-primary btn-sm w-100" target="_blank">
                View Series Page
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card mt-3">
        <div class="card-header bg-white border-0">
          <h6 class="mb-0">Quick Actions</h6>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="/website-popmart/admin/product_form.php?series_id=<?php echo (int)$s['id']; ?>"
               class="btn btn-outline-success btn-sm">
              Add Product
            </a>
            <button class="btn btn-outline-danger btn-sm"
                    onclick="deleteSeries(<?php echo (int)$s['id']; ?>, '<?php echo htmlspecialchars($s['name']); ?>')">
              Delete Series
            </button>
          </div>
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
    if (confirm('Are you sure you want to delete the series "' + name + '"?\n\nThis will permanently remove the series and cannot be undone. Products in this series will remain but may need to be reassigned.')) {
      document.getElementById('deleteId').value = id;
      document.getElementById('deleteForm').submit();
    }
  }
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
