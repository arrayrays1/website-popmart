<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

// prefer unified table if present; also load legacy contact_messages for visibility
$queries = [];
try {
  $rows = $pdo->query("SELECT id, name, email, message, is_registered, created_at, replied_at, 'customer_queries' as src FROM customer_queries ORDER BY created_at DESC")->fetchAll();
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
  <h1>Customer Queries</h1>
  <div style="overflow:auto;">
    <table class="table" style="width:100%; border-collapse: collapse; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Message</th>
          <th>Date Submitted</th>
          <th>Source</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($queries as $q): ?>
          <tr>
            <td>#<?php echo (int)$q['id']; ?></td>
            <td><?php echo htmlspecialchars($q['name']); ?></td>
            <td><?php echo htmlspecialchars($q['email']); ?></td>
            <td style="max-width:520px; white-space:pre-wrap;"><?php echo nl2br(htmlspecialchars($q['message'])); ?></td>
            <td class="muted"><?php echo htmlspecialchars($q['created_at'] ?? ''); ?></td>
            <td><?php echo $q['src']==='customer_queries' ? 'Customer Queries' : 'Contact Messages'; ?></td>
            <td>
              <?php if ($q['src']==='customer_queries'): ?>
                <?php if (!empty($q['replied_at'])): ?>
                  <span class="badge" style="background:#e6ffe6; color:#065f46;">Resolved</span>
                <?php else: ?>
                  <span class="badge" style="background:#fff7ed; color:#92400e;">Open</span>
                <?php endif; ?>
              <?php else: ?>
                <span class="badge" style="background:#f3f4f6; color:#374151;">N/A</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($q['src']==='customer_queries' && empty($q['replied_at'])): ?>
                <form method="post" action="/website-popmart/admin/query_resolve.php">
                  <input type="hidden" name="id" value="<?php echo (int)$q['id']; ?>" />
                  <button class="btn" type="submit">Mark as Resolved</button>
                </form>
              <?php else: ?>
                <span>-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
