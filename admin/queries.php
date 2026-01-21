<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$queries = [];
try {
  $rows = $pdo->query("SELECT id, query_id, name, email, message, is_registered, created_at, replied_at, 'customer_queries' as src FROM customer_queries ORDER BY created_at DESC")->fetchAll();
  $queries = array_merge($queries, $rows);
} catch (PDOException $e) { /* table might not exist yet */ }

try {
  $rows2 = $pdo->query("SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC")->fetchAll();
  foreach ($rows2 as $r) {
    $queries[] = [
      'id' => $r['id'],
      'name' => $r['name'],
      'email' => $r['email'],
      'message' => $r['message'],
      'is_registered' => null,
      'created_at' => $r['created_at'],
      'replied_at' => null,
      'src' => 'contact_messages'
    ];
  }
} catch (PDOException $e) { /* ignore */ }
?>
<?php $activePage = 'queries'; require_once __DIR__ . '/includes/header.php'; ?>

<?php
$totalQueries = 0;
try {
  // Count total queries from both tables
  $customerQueries = (int)$pdo->query("SELECT COUNT(*) FROM customer_queries")->fetchColumn();
  $contactMessages = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
  $totalQueries = $customerQueries + $contactMessages;
} catch (PDOException $e) {
  $totalQueries = 0;
}
?>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="page-title">Customer Queries</h1>
      <p class="page-subtitle">View and monitor all customer inquiries and feedback</p>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body text-center py-4">
          <h3 class="mb-0 fw-bold text-primary"><?php echo $totalQueries; ?></h3>
          <small class="text-muted">Total Customer Queries</small>
        </div>
      </div>
    </div>
  </div>
  
  <div class="table-container">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Query ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Message</th>
          <th>Date</th>
          <th>Source</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($queries as $q): ?>
          <tr>
            <td><span class="badge bg-info"><?php echo htmlspecialchars($q['query_id'] ?? $q['id']); ?></span></td>
            <td><?php echo htmlspecialchars($q['name']); ?></td>
            <td><small class="text-muted"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($q['email']); ?></small></td>
            <td>
              <?php echo nl2br(htmlspecialchars(trim($q['message']))); ?>
            </td>
            <td style="white-space: nowrap;"><small><?php echo date('M d, Y', strtotime($q['created_at'] ?? 'now')); ?></small></td>
            <td>
              <span class="badge <?php echo $q['src']==='customer_queries' ? 'bg-primary' : 'bg-secondary'; ?>">
                <?php echo $q['src']==='customer_queries' ? 'Customer' : 'Contact'; ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
