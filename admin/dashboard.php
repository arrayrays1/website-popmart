<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

function getScalar($pdo, $sql, $params = []) {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchColumn();
}

$totalSales = (float)(getScalar($pdo, "SELECT COALESCE(SUM(total),0) FROM orders WHERE status = 'Delivered'"));
$totalUsers = (int)(getScalar($pdo, "SELECT COUNT(*) FROM users WHERE role = 'customer'"));
$pendingCount = (int)(getScalar($pdo, "SELECT COUNT(*) FROM orders WHERE status != 'Delivered'"));
$completedCount = (int)(getScalar($pdo, "SELECT COUNT(*) FROM orders WHERE status = 'Delivered'"));
$totalProducts = (int)(getScalar($pdo, "SELECT COUNT(*) FROM products"));

$salesRows = $pdo->query("SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS s
                          FROM orders
                          WHERE status='Delivered' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY d ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$sales7 = [];
for ($i = 6; $i >= 0; $i--) {
  $day = (new DateTime())->modify("-{$i} day")->format('Y-m-d');
  $sales7[] = (float)($salesRows[$day] ?? 0);
}
$maxBar = max($sales7) ?: 1;

$sql = "
  SELECT o.id, o.user_id, o.total, o.status, o.created_at,
         CONCAT(u.first_name, ' ', u.last_name) AS customer,
         GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') AS items
  FROM orders o
  JOIN users u ON u.id = o.user_id
  JOIN order_items oi ON oi.order_id = o.id
  JOIN products p ON p.id = oi.product_id
  WHERE o.status != 'Delivered'
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT 10
";
$pendingOrders = $pdo->query($sql)->fetchAll();
$recentUsers = [];
try {
  $recentUsers = $pdo->query("SELECT first_name, last_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* ignore */ }
$latestQuery = null;
try {
  $latestQuery = $pdo->query("SELECT query_id, name, email, message, created_at FROM customer_queries ORDER BY created_at DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

function nextStatus($current) {
  $map = [
    'Pending' => 'To Ship',
    'To Ship' => 'To Deliver',
    'To Deliver' => 'Delivered'
  ];
  return $map[$current] ?? null;
}

?>
<?php $activePage = 'dashboard'; require_once __DIR__ . '/includes/header.php'; ?>

<div class="row mb-4">
  <div class="col-12">
    <h1 class="page-title">Dashboard Overview</h1>
    <p class="page-subtitle">Welcome back! Here's what's happening with your store today</p>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h6 class="text-muted mb-2">Total Sales</h6>
        <h3 class="mb-0 fw-bold text-success">₱<?php echo number_format($totalSales, 2); ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h6 class="text-muted mb-2">Total Customers</h6>
        <h3 class="mb-0 fw-bold text-primary"><?php echo $totalUsers; ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h6 class="text-muted mb-2">Active Orders</h6>
        <h3 class="mb-0 fw-bold text-warning"><?php echo $pendingCount; ?></h3>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h6 class="text-muted mb-2">Delivered Orders</h6>
        <h3 class="mb-0 fw-bold text-info"><?php echo $completedCount; ?></h3>
      </div>
    </div>
  </div>
</div>

  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Active Orders</h4>
      <a href="/website-popmart/admin/orders.php" class="btn btn-sm" style="background-color: #f80000; color: white; border-color: #f80000;">
        <i class="bi bi-eye me-1"></i>View All Orders
      </a>
    </div>
  </div>
  
  <div class="table-container">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Products</th>
          <th>Total</th>
          <th>Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingOrders as $row): ?>
          <tr>
            <td><span class="badge" style="background-color: #f80000;">#<?php echo htmlspecialchars($row['id']); ?></span></td>
            <td><?php echo htmlspecialchars($row['customer']); ?></td>
            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($row['items']); ?></td>
            <td class="fw-bold text-success">₱<?php echo number_format((float)$row['total'], 2); ?></td>
            <td>
              <span class="badge <?php
                switch($row['status']) {
                  case 'Pending': echo 'bg-warning text-dark'; break;
                  case 'To Ship': echo 'bg-info'; break;
                  case 'To Deliver': echo 'bg-primary'; break;
                  default: echo 'bg-secondary';
                }
              ?>"><?php echo htmlspecialchars($row['status']); ?></span>
            </td>
            <td class="text-center">
              <a href="/website-popmart/admin/orders.php" class="btn btn-sm" style="background-color: #f80000; color: white; border-color: #f80000;">
                <i class="bi bi-arrow-right-circle me-1"></i>Manage
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="row g-4 mt-2">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Recent Sign-ups</h5>
        </div>
        <div class="card-body">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $u): ?>
              <tr>
                <td><?php echo htmlspecialchars(($u['first_name'] ?? '').' '.($u['last_name'] ?? '')); ?></td>
                <td><small class="text-muted"><?php echo htmlspecialchars($u['email'] ?? ''); ?></small></td>
                <td><small><?php echo date('M d, Y', strtotime($u['created_at'] ?? 'now')); ?></small></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recentUsers)): ?>
              <tr><td colspan="3" class="text-center text-muted">No recent sign-ups</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Latest Customer Query</h5>
          <a href="/website-popmart/admin/queries.php" class="btn btn-sm" style="background-color: #f80000; color: white; border-color: #f80000;">
            <i class="bi bi-eye me-1"></i>View All
          </a>
        </div>
        <div class="card-body">
        <?php if ($latestQuery): ?>
          <div class="alert alert-light border mb-0">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1"><?php echo htmlspecialchars($latestQuery['name']); ?></h6>
                <span class="badge" style="background-color: #f80000;"><?php echo htmlspecialchars($latestQuery['query_id']); ?></span>
              </div>
              <small class="text-muted">
                <?php echo date('M d, Y H:i', strtotime($latestQuery['created_at'])); ?>
              </small>
            </div>
            <p class="mb-2">
              <i class="bi bi-envelope me-1"></i>
              <small class="text-muted"><?php echo htmlspecialchars($latestQuery['email']); ?></small>
            </p>
            <p class="mb-0 text-muted">
              <?php echo htmlspecialchars(substr($latestQuery['message'], 0, 120)) . (strlen($latestQuery['message']) > 120 ? '...' : ''); ?>
            </p>
          </div>
        <?php else: ?>
          <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">No customer queries yet</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
